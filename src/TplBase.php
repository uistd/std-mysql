<?php

namespace UiStd\Mysql;

/**
 * Class MysqlDaoTpl
 * @package UiStd\Mysql
 */
abstract class TplBase
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * @var MysqlInterface
     */
    private $mysql_obj;

    /**
     * @var string 数据库配置
     */
    protected $mysql_config_name = 'main';

    /**
     * @var array
     */
    private $where_arr;

    /**
     * @return MysqlInterface
     */
    public function getDb()
    {
        if (null === $this->mysql_obj) {
            $this->mysql_obj = MysqlFactory::get($this->mysql_config_name);
        }
        return $this->mysql_obj;
    }

    /**
     * 获取实例
     * @return static
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 影响行数
     * @return int
     */
    public function affectRows()
    {
        return $this->getDb()->affectRows();
    }

    /**
     * 最近插入的行
     * @return int
     */
    public function lastInsertId()
    {
        return $this->getDb()->lastInsertId();
    }

    /**
     * 清空where
     * @return self
     */
    protected function whereInit()
    {
        $this->where_arr = array();
        return $this;
    }

    /**
     * 设置where
     * @param string $key
     * @param int|string $value
     */
    protected function whereSet($key, $value)
    {
        $this->where_arr[] = '`'.$key.'`="'.$value.'"';
    }

    /**
     * 增加where条件
     * @param string $where_str
     */
    protected function whereAdd($where_str)
    {
        $this->where_arr[] = $where_str;
    }

    /**
     * 附加where条件
     * @param array $arr
     */
    protected function whereAppend(array $arr)
    {
        foreach ($arr as $key => $value) {
            $this->where_arr[] = '`' . $key . '` = "' . $value . '"';
        }
    }

    /**
     * dump出where
     * @return string
     */
    protected function whereDump()
    {
        if (empty($this->where_arr)) {
            return '';
        }
        $result = join(' AND ', $this->where_arr);
        $this->where_arr = null;
        return $result;
    }
}
