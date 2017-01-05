<?php
namespace ffan\php\mysql;

use ffan\php\utils\Config as FfanConfig;

/**
 * Class Query 生成查询语句
 * @package ffan\php\mysql
 */
class Query
{
    /**
     * and 条件
     */
    const LOGIC_AND = 'AND';

    /**
     * or 条件
     */
    const LOGIN_OR = 'OR';

    /**
     * 倒序
     */
    const DESC = 'DESC';

    /**
     * 顺序
     */
    const ASC = 'ASC';

    /**
     * @var string 表名
     */
    private $table_name;

    /**
     * @var string 配置名
     */
    private $conf_name;

    /**
     * @var array where条件
     */
    private $where_arr;

    /**
     * @var array 排序
     */
    private $order_arr;

    /**
     * @var array group by
     */
    private $group_arr;

    /**
     * @var int 记录开始条数
     */
    private $start_row;

    /**
     * @var int 限制条件
     */
    private $limit;

    /**
     * @var string sql语句类型
     */
    private $sql_type;

    /**
     * @var string 字段
     */
    private $fields;

    /**
     * @var array 更新字段
     */
    private $extra_data;

    /**
     * @var array 附加的参数
     */
    private $extra_option;

    /**
     * QueryInterface constructor.
     * @param string $config_name 配置名（根据配置自动实现分表等功能）
     */
    public function __construct($config_name = 'main')
    {
        $this->conf_name = $config_name;
    }

    /**
     * 设置表名，如果设置了hash_id，根据配置自动生成表名后缀
     * @param string $table_name 表名
     * @param int $hash_id 用于分表的Id
     * @return $this
     */
    public function tableName($table_name, $hash_id = 0)
    {
        $this->table_name = $table_name;
        if ($hash_id <= 0) {
            return $this;
        }
        //如果有配置表名hash后缀
        $conf_arr = FfanConfig::get(Mysql::CONFIG_KEY . '.' . $this->conf_name);
        if (isset($conf_arr['table_hash']) && ($conf_arr['table_hash'][$table_name])) {
            $table_num = (int)$conf_arr['table_hash'][$table_name];
            $this->table_name .= '_' . ($hash_id % $table_num);
        }
        return $this;
    }

    /**
     * 生成一个where条件
     * @param string $field 条件
     * @param string $operator 操作符
     * @param mixed $value 值
     * @param string $logic 如果是多个条件，条件之间的关系
     * @param bool $in_bracket 是否放入括号中
     * @return $this
     */
    public function where($field, $operator, $value = null, $logic = self::LOGIC_AND, $in_bracket = false)
    {
        $this->where_arr[] = array(
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'logic' => $logic,
            'in_bracket' => $in_bracket
        );
        return $this;
    }

    /**
     * 不在
     */
    public function notIn()
    {
        
    }

    /**
     * 
     */
    public function in()
    {
        
    }

    /**
     * sql语句的order部分
     * @param string $order_fields 排序字段
     * @param string $order_type 排序类型
     * @return $this
     */
    public function order($order_fields, $order_type)
    {
        $this->order_arr[$order_fields] = $order_type;
        return $this;
    }

    /**
     * sql语句的group部分
     * @param string $field 字段名
     * @return $this
     */
    public function group($field)
    {
        $this->group_arr[] = $field;
        return $this;
    }

    /**
     * 设置SQL语句的limit部分
     * @param int $start_row
     * @param int $limit
     * @return $this
     */
    public function limit($start_row = 0, $limit = 1)
    {
        $start_row = (int)$start_row;
        $limit = (int)$limit;
        if ($start_row < 0) {
            throw new \InvalidArgumentException('start_row 不能小于0');
        }
        if ($limit < 1) {
            throw new \InvalidArgumentException('limit 不能小于1');
        }
        $this->start_row = $start_row;
        $this->limit = (int)$limit;
        return $this;
    }

    /**
     * 生成查询SQL语句
     * @param string $fields 字段
     * @param int $limit 限制 默认是1000条，0 表示不限制
     * @return $this
     */
    public function select($fields = '*', $limit = 1000)
    {
        $this->fields = $fields;
        $this->setType('SELECT');
        if ($limit > 0) {
            $this->limit(0, $limit);
        }
        return $this;
    }

    /**
     * 生成更新SQL语句
     * @param array $data 更新的数据
     * @param int $limit 限制条数 默认是1条，0：表示不限制
     * @return $this
     */
    public function update($data, $limit = 1)
    {
        $this->setType('UPDATE');
        $this->extra_data = $data;
        if ($limit > 0) {
            $this->limit(0, $limit);
        }
        return $this;
    }

    /**
     * 生成删除记录的SQL语句
     * @param int $limit 限制条数 0 表示不限制
     * @return $this
     */
    public function delete($limit = 1)
    {
        $this->setType('DELETE');
        if ($limit > 0) {
            $this->limit(0, $limit);
        }
        return $this;
    }

    /**
     * 生成插入的SQL语句
     * @param array $data
     * @return $this
     */
    public function insert($data)
    {
        $this->setType('INSERT');
        $this->extra_data = $data;
        return $this;
    }

    /**
     * 去重
     * @return $this
     */
    public function distinct()
    {
        $this->extra_option['distinct'] = true;
        return $this;
    }

    /**
     * 设置查询的 top 关键字
     * @param int $num 数字 或者 百分比
     * @param null|string $fields
     * @return $this
     */
    public function top($num, $fields = null)
    {
        $this->extra_option['top'] = array(
            'num' => $num,
            'fields' => $fields
        );
        return $this;
    }
    
    /**
     * mysql 的 top函数
     * @param string $field 字段名
     * @param null|string $alias 别名
     * @return $this
     */
    public function avg($field, $alias = null) {
        $this->extra_option['func'][] = ['AVG', $field, $alias];
        return $this;
    }

    /**
     * mysql 的 count 函数
     * @param string $field 字段名
     * @param null|string $alias 别名
     * @return $this
     */
    public function count($field, $alias = null)
    {
        $this->extra_option['func'][] = ['COUNT', $field, $alias];
        return $this;
    }

    /**
     * mysql 的 max 函数
     * @param string $field 字段名
     * @param null|string $alias 别名
     * @return $this
     */
    public function max($field, $alias = null)
    {
        $this->extra_option['func'][] = ['MAX', $field, $alias];
        return $this;
    }

    /**
     * mysql 的 min 函数
     * @param string $field 字段名
     * @param null|string $alias 别名
     * @return $this
     */
    public function min($field, $alias = null)
    {
        $this->extra_option['func'][] = ['MIN', $field, $alias];
        return $this;
    }

    /**
     * 生成sql语句
     * @return string
     */
    public function toSql()
    {

    }

    /**
     * 设置sql类型
     * @param string $type 类型
     * @throws MysqlException
     */
    private function setType($type)
    {
        if (null !== $this->sql_type) {
            throw new MysqlException('该Query已经设置为：' . $this->sql_type, MysqlException::QUERY_SYNTAX_ERROR);
        }
        $this->sql_type = $type;
    }
}