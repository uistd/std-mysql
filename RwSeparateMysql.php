<?php
namespace ffan\php\mysql;

/**
 * Class RwSeparateMysql 读写分离的mysql操作类
 * @package ffan\php\mysql
 */
class RwSeparateMysql implements MysqlInterface
{
    /**
     * @var string 主库配置名称
     */
    private $master_config;

    /**
     * @var string 从库配置名称
     */
    private $slave_config;

    /**
     * @var Mysql 主库操作对象
     */
    private $master_object;

    /**
     * @var Mysql 从库操作对象
     */
    private $slave_object;

    /**
     * @var bool 是否强制使用从库，仅对获取从库时生效
     * （避免主从同步不及时，刚写入主库的数据，如果立即要查出来，从库数据可能不存在）
     */
    private $is_force_slave = false;

    /**
     * RwSeparateMysql constructor.
     * @param string $master_config 主库配置名称
     * @param string $slave_config 从库配置名称
     */
    public function __construct($master_config = 'master', $slave_config = 'slave')
    {
        $this->master_config = $master_config;
        $this->slave_config = $slave_config;
    }

    /**
     * 获取主库操作对象
     */
    private function getMaster()
    {
        if (!$this->master_object) {
            $this->master_object = new Mysql($this->master_config);
        }
        return $this->master_object;
    }

    /**
     * 强制使用从库设置
     * @param bool $flag
     */
    public function setForceSlave($flag = true)
    {
        $this->is_force_slave = $flag;
    }

    /**
     * 获取从库操作对象 如果主库已经连接，优先使用主库，除非已经设计强制作用从库
     * @return Mysql
     */
    private function getSlave()
    {
        //如果已经连接了主库，就一直在主库操作，不再切到从库，除非强制使用从库
        if ($this->master_object && !$this->is_force_slave) {
            return $this->master_object;
        }
        if (!$this->slave_object) {
            $this->slave_object = new Mysql($this->slave_object);
        }
        return $this->slave_object;
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
        return $this->getSlave()->getRow($query_sql, $class_name);
    }

    /**
     * 获取查询结果的第一条记录的第一个字段
     * @param $query_sql
     * @return string|null
     * @throws MysqlException
     */
    public function getFirstCol($query_sql)
    {
        return $this->getSlave()->getFirstCol($query_sql);
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
        return $this->getSlave()->getMultiRow($query_sql, $class_name);
    }

    /**
     * 获取多条记录，以枚举数组返回
     * @param string $query_sql SQL语句
     * @param string $index_col 做为key的字段名
     * @param null|string $class_name
     * @return array|object
     * @throws MysqlException
     */
    public function getMultiAssocRow($query_sql, $index_col, $class_name = null)
    {
        return $this->getSlave()->getMultiAssocRow($query_sql, $index_col, $class_name);
    }

    /**
     * 所有查询记录的第一个字段，以数组返回
     * @param string $query_sql SQL语句
     * @return array
     * @throws MysqlException
     */
    public function getMultiFirstCol($query_sql)
    {
        return $this->getSlave()->getMultiFirstCol($query_sql);
    }

    /**
     * 返回一个key=>value格式的数组，第一个字段为key，第二个字段为value
     * @param string $query_sql SQL语句
     * @return array
     * @throws MysqlException
     */
    public function getMultiAssocCol($query_sql)
    {
        return $this->getSlave()->getMultiAssocCol($query_sql);
    }

    /**
     * 执行一条SQL语句
     * @param string $query_sql
     * @return void
     * @throws MysqlException
     */
    public function query($query_sql)
    {
        //主库已经有了，也没有强制使用从库
        if ($this->master_object || $this->is_force_slave) {
            $this->master_object->query($query_sql);
        }
        $is_write = MysqlCommon::isWriteSql($query_sql);
        if ($is_write) {
            $this->getMaster()->query($query_sql);
        } else {
            $this->getSlave()->query($query_sql);
        }
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
        return $this->getMaster()->update($table, $data, $condition, $limit);
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
        $this->getMaster()->insert($table, $data);
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
        return $this->getMaster()->delete($table, $condition, $limit);
    }

    /**
     * 取得最后的last_insert_id
     * @return int
     */
    public function lastInsertId()
    {
        return $this->getMaster()->lastInsertId();
    }

    /**
     * 获取上一次执行影响的记录数
     * @return int
     */
    public function affectRows()
    {
        return $this->getMaster()->affectRows();
    }

    /**
     * 提交变更
     * @return void
     * @throws MysqlException
     */
    public function commit()
    {
        $this->getMaster()->commit();
    }

    /**
     * 回滚
     * @return void
     * @throws MysqlException
     */
    public function rollback()
    {
        $this->getMaster()->rollback();
    }

    /**
     * 是否与服务端保持连接
     * @return bool
     */
    public function ping()
    {
        return $this->getMaster()->ping() && $this->getSlave()->ping();
    }

    /**
     * 关闭
     * @return void
     */
    public function close()
    {
        if ($this->master_object) {
            $this->master_object->close();
        }
        if ($this->slave_object) {
            $this->slave_object->close();
        }
    }
}
