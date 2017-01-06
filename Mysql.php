<?php
namespace ffan\php\mysql;

use ffan\php\utils\Config as FfanConfig;
use ffan\php\utils\InvalidConfigException;
use Psr\Log\LoggerInterface;
use ffan\php\logger\LoggerFactory;

/**
 * Class Mysql Mysql操作类
 * @package ffan\php\mysql
 */
class Mysql implements MysqlInterface
{
    /**
     * mysql has gone away错误的错误ID
     */
    const MYSQL_GONE_AWAY = 2006;

    /**
     * 配置名
     */
    const CONFIG_KEY = 'ffan-mysql';

    /**
     * @var string 配置名称
     */
    private $config_name;

    /**
     * @var bool 是否已经创建连接
     */
    private $is_connect = false;

    /**
     * @var LoggerInterface 日志对象
     */
    private $logger;

    /**
     * @var bool 是否需要commit的标志
     */
    private $commit_flag = false;

    /**
     * @var \mysqli 连接对象
     */
    private $link_obj;

    /**
     * @var int 慢查询认定时间
     */
    private $slow_query_time;

    /**
     * @var bool 是否是mysql断开自动重连
     */
    private $is_retry = false;

    /**
     * Mysql constructor.
     * @param string $config_name 配置名称
     * @param LoggerInterface|null $logger 日志
     */
    public function __construct($config_name = 'master', LoggerInterface $logger = null)
    {
        $this->config_name = $config_name;
        if (null !== $logger) {
            $this->logger = $logger;
        }
    }

    /**
     * 连接服务器
     * @throws InvalidConfigException
     */
    private function connect()
    {
        $config_key = self::CONFIG_KEY . '.' . $this->config_name;
        $conf_arr = FfanConfig::get($config_key);
        if (!is_array($conf_arr)) {
            throw new InvalidConfigException($config_key);
        }
        $host = $this->getConfigItem($conf_arr, 'host', '127.0.0.1');
        $user = $this->getConfigItem($conf_arr, 'user');
        $password = $this->getConfigItem($conf_arr, 'password');
        $database = $this->getConfigItem($conf_arr, 'database');
        $port = $this->getConfigItem($conf_arr, 'port', 3306);
        $link_obj = new \mysqli($host, $user, $password, $database, $port);
        if ($link_obj->connect_errno) {
            throw new MysqlException($link_obj->connect_error, MysqlException::CONNECT_FAIL);
        }
        $this->logMsg('connect {user}@{host}:{port} success, use database:{database}', $conf_arr);
        $this->is_connect = true;
        $charset = $this->getConfigItem($conf_arr, 'charset', 'utf8');
        $link_obj->set_charset($charset);
        $this->slow_query_time = $this->getConfigItem($conf_arr, 'slow_query_time', 100);
        $this->link_obj = $link_obj;
    }

    /**
     * 检查配置项
     * @param array $conf_arr 配置数组
     * @param string $item_name 配置项名称
     * @param null $default 如果值不存在，默认值
     * @return string
     * @throws InvalidConfigException
     */
    private function getConfigItem(&$conf_arr, $item_name, $default = null)
    {
        if (isset($conf_arr[$item_name])) {
            return trim((string)$conf_arr[$item_name]);
        }
        if (null !== $default) {
            $conf_arr[$item_name] = $default;
            return $default;
        }
        throw new InvalidConfigException(self::CONFIG_KEY . '.' . $this->config_name . '.' . $item_name);
    }

    /**
     * 执行一次查询
     * @param string $query_sql SQL语句
     * @param bool $is_write 是否是写操作
     * @return \mysqli_result
     * @throws InvalidConfigException
     * @throws MysqlException
     */
    private function executeQuery($query_sql, $is_write = false)
    {
        if (!$this->is_connect) {
            $this->connect();
        }
        if ($is_write && !$this->commit_flag) {
            $this->commit_flag = true;
            $this->executeQuery('BEGIN');
        }
        $time = microtime(true);
        $res = $this->link_obj->query($query_sql);
        $run_time = round((microtime(true) - $time) * 1000, 2);
        $log_content = "Query: {query}\nAffect rows: {affect_row} \nQuery time: {query_time}ms\n--------------------------------------\n";
        $this->logMsg($log_content, ['affect_row' => $this->link_obj->affected_rows, 'query_time' => $run_time]);
        //记录慢查询
        if ($this->slow_query_time > 0 && $run_time > $this->slow_query_time && 'COMMIT' !== $query_sql) {
            $this->logSlowQuery($query_sql, (int)$run_time);
        }
        if (false === $res) {
            return $this->queryError($query_sql, $is_write);
        }
        return $res;
    }

    /**
     * 查询出错处理
     * @param string $sql SQL语句
     * @param bool $is_write 是否是写操作
     * @return \mysqli_result
     * @throws MysqlException
     */
    private function queryError($sql, $is_write)
    {
        //MySql server has gone away. 错误
        if (self::MYSQL_GONE_AWAY !== $this->link_obj->errno || $this->is_retry) {
            throw new MysqlException($this->link_obj->error . '(code:' . $this->link_obj->errno . ')', MysqlException::QUERY_FAIL);
        }
        $this->link_obj = null;
        $this->is_retry = true;
        $re = $this->ping();
        if (!$re) {
            throw new MysqlException('Mysql已经断开连接，尝试重连失败', MysqlException::MYSQL_GONE_AWAY);
        }
        $result = $this->executeQuery($sql, $is_write);
        //重置retry标志
        $this->is_retry = false;
        return $result;
    }

    /**
     * 获取一条记录
     * @param string $query_sql SQL语句
     * @param null|string $class_name 对象名称，如果传入非NULL，会返回对象
     * @return array|object|null
     * @throws MysqlException
     */
    public function getRow($query_sql, $class_name = null)
    {
        $res_data = $this->executeQuery($query_sql);
        if (!$res_data) {
            return null;
        }
        //指定了类名，返回对象
        if (null !== $class_name) {
            $row = $res_data->fetch_object($class_name);
        } else {
            $row = $res_data->fetch_assoc();
        }
        $res_data->free();
        return $row;
    }

    /**
     * 获取查询结果的第一条记录的第一个字段
     * @param $query_sql
     * @return string|null
     * @throws MysqlException
     */
    public function getFirstCol($query_sql)
    {
        $res_data = $this->executeQuery($query_sql);
        if (!$res_data) {
            return null;
        }
        $row = $res_data->fetch_row();
        $res_data->free();
        return $row[0];
    }

    /**
     * 获取多条记录，以枚举数组返回
     * @param string $query_sql SQL语句
     * @param null|string $class_name 对象名称，如果传入非NULL，会返回对象
     * @return array|object
     * @throws MysqlException
     */
    public function getMultiRow($query_sql, $class_name = null)
    {
        $res = $this->executeQuery($query_sql);
        $rows = array();
        if (!$res) {
            return $rows;
        }
        if (null !== $class_name) {
            while ($row = $res->fetch_object()) {
                $rows[] = $row;
            }
        } else {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $res->free();
        return $rows;
    }

    /**
     * 获取多条记录，以枚举数组返回
     * @param string $query_sql SQL语句
     * @param string $index_col 做为key的字段名
     * @param null|string $class_name
     * @return array
     * @throws MysqlException
     */
    public function getMultiAssocRow($query_sql, $index_col, $class_name = null)
    {
        $res = $this->executeQuery($query_sql);
        $rows = array();
        if (!$res) {
            return $rows;
        }
        if (null !== $class_name) {
            while ($row = $res->fetch_object()) {
                if (property_exists($row, $index_col)) {
                    $rows[$row->$index_col] = $row;
                } else {
                    $rows[] = $row;
                }
            }
        } else {
            while ($row = $res->fetch_assoc()) {
                if (isset($row[$class_name])) {
                    $rows[$class_name] = $row;
                } else {
                    $rows[] = $row;
                }
            }
        }
        $res->free();
        return $rows;
    }

    /**
     * 所有查询记录的第一个字段，以数组返回
     * @param string $query_sql SQL语句
     * @return array
     * @throws MysqlException
     */
    public function getMultiFirstCol($query_sql)
    {
        $res_data = $this->executeQuery($query_sql);
        $res_rows = array();
        if (!$res_data) {
            return $res_rows;
        }
        while ($row = $res_data->fetch_row()) {
            $res_rows[] = $row[0];
        }
        $res_data->free();
        return $res_rows;
    }

    /**
     * 返回一个key=>value格式的数组，第一个字段为key，第二个字段为value
     * @param string $query_sql SQL语句
     * @return array
     * @throws MysqlException
     */
    public function getMultiAssocCol($query_sql)
    {
        $res_data = $this->executeQuery($query_sql);
        $res_rows = array();
        if (!$res_data) {
            return $res_rows;
        }
        while ($row = $res_data->fetch_row()) {
            $res_rows[$row[0]] = isset($row[1]) ? $row[1] : null;
        }
        $res_data->free();
        return $res_rows;
    }

    /**
     * 更新记录
     * @param string $table 表名
     * @param array $data 数据
     * @param string $condition 条件
     * @param int $limit 限制条数 0：不限制
     * @return int 影响条数
     * @throws MysqlException
     */
    public function update($table, array $data, $condition, $limit = 1)
    {
        $new_sets = array();
        foreach ($data as $col_item => $val_item) {
            $new_sets[] = '`' . $col_item . "`='" . $val_item . "'";
        }
        $query_sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $new_sets) . ' WHERE ' . $condition;
        $limit = (int)$limit;
        if ($limit > 0) {
            $query_sql .= ' LIMIT ' . $limit;
        }
        $this->executeQuery($query_sql, true);
        return $this->affectRows();
    }

    /**
     * 插入一条或者多条数据
     * @param string $table
     * @param array $data
     * @return void
     * @throws MysqlException
     */
    public function insert($table, array $data)
    {
        if (empty($data)) {
            return;
        }
        $fields_arr = array();
        $value_arr = array();
        //如果数组第一项 还是数据，表示是一次插入多条数据
        if (is_array(current($data))) {
            $index = 0;
            foreach ($data as $each_data) {
                $each_arr = array();
                foreach ($each_data as $col => $val) {
                    if (0 === $index++) {
                        $fields_arr[] = $col;
                    }
                    $each_arr[] = $val;
                }
                $value_arr[] = "('" . join("','", $each_arr) . "')";
            }
        } //只有一项
        else {
            foreach ($data as $col_item => $val_item) {
                $fields_arr[] = $col_item;
                $value_arr[] = $val_item;
            }
        }
        //这里故意省去 INSERT 因为PHPStorm有BUG
        $sql = 'INTO `' . $table . '` ( `' . join(',', $fields_arr) . "`) VALUES ('" . join(',', $value_arr) . "')";
        $this->executeQuery('INSERT ' . $sql, true);
    }
    
    /**
     * 删除记录
     * @param string $table 表名
     * @param string $condition 条件
     * @param int $limit 限制记录 0：不限制
     * @return int 影响条数
     * @throws MysqlException
     */
    public function delete($table, $condition, $limit = 1)
    {
        $sql = 'FROM '. $table .' WHERE '. $condition;
        $limit = (int)$limit;
        if ($limit > 0) {
            $sql .= ' LIMIT '. $limit;
        }
        $this->executeQuery('DELETE '. $sql, true);
        return $this->affectRows();
    }

    /**
     * 执行一条SQL语句
     * @param string $query_sql
     * @return void
     * @throws MysqlException
     */
    public function query($query_sql)
    {
        $type_str = strtoupper(substr($query_sql, 0, 6));
        //sql语句只要不是select，都是写操作
        $is_write = 'SELECT' !== $type_str;
        $this->executeQuery($query_sql, $is_write);
    }

    /**
     * 取得最后的last_insert_id
     * @return int
     */
    public function lastInsertId()
    {
        return $this->link_obj ? $this->link_obj->insert_id : -1;
    }

    /**
     * 获取上一次执行影响的记录数
     * @return int
     */
    public function affectRows()
    {
        return $this->link_obj ? $this->link_obj->affected_rows : 0;
    }

    /**
     * 提交变更
     * @return void
     * @throws MysqlException
     */
    public function commit()
    {
        if (!$this->commit_flag) {
            return;
        }
        $this->executeQuery('COMMIT');
        $this->commit_flag = false;
    }

    /**
     * 回滚
     * @return void
     * @throws MysqlException
     */
    public function rollback()
    {
        if (!$this->commit_flag) {
            return;
        }
        $this->commit_flag = false;
        $this->executeQuery('ROLLBACK');
    }

    /**
     * 是否与服务端保持连接
     * @return bool
     */
    public function ping()
    {
        if (!$this->link_obj) {
            return false;
        }
        $re = $this->link_obj->ping();
        if (!$re) {
            $this->link_obj->close();
            $this->link_obj = null;
        }
        return $re;
    }

    /**
     * 关闭
     * @return void
     */
    public function close()
    {
        if ($this->link_obj) {
            $this->link_obj->close();
        }
    }

    /**
     * 记录日志消息
     * @param string $content 消息内容
     * @param array $data 消息变量替换数据
     */
    private function logMsg($content, $data = null)
    {
        if (null === $this->logger) {
            $this->logger = LoggerFactory::get();
        }
        $this->logger->debug('[MYSQL][' . $this->config_name . ']' . $content, $data);
    }

    /**
     * 记录慢查询
     * @param string $sql SQL语句
     * @param int $slow_time 时间
     */
    private function logSlowQuery($sql, $slow_time)
    {
        //这里写死，暂时没有觉得需要配置
        LoggerFactory::get('slow_query')->warning('[MYSQL][' . $this->config_name . '][' . $slow_time . 'ms]' . $sql);
    }
}