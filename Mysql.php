<?php
namespace ffan\php\mysql;

/**
 * Class Mysql Mysql操作类
 * @package ffan\php\mysql
 */
class Mysql implements MysqlInterface
{
    /**
     * 配置名
     */
    const CONFIG_KEY = 'ffan-mysql';
    
    /**
     * 获取一条记录
     * @param string $query_sql SQL语句
     * @param null|string $class_name 对象名称，如果传入非NULL，会返回对象
     * @return array|object|null
     * @throws MysqlException
     */
    public function getRow($query_sql, $class_name = null)
    {
        // TODO: Implement getRow() method.
    }

    /**
     * 获取查询结果的第一条记录的第一个字段
     * @param $query_sql
     * @return string|false
     * @throws MysqlException
     */
    public function getFirstCol($query_sql)
    {
        // TODO: Implement getFirstCol() method.
    }

    /**
     * 获取多条记录，以枚举数组返回
     * @param string $query_sql SQL语句
     * @param null|string $class_name 对象名称，如果传入非NULL，会返回对象
     * @return array
     * @throws MysqlException
     */
    public function getMultiRow($query_sql, $class_name = null)
    {
        // TODO: Implement getMultiRow() method.
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
        // TODO: Implement getMultiAssocRow() method.
    }

    /**
     * 所有查询记录的第一个字段，以数组返回
     * @param string $query_sql SQL语句
     * @return array
     * @throws MysqlException
     */
    public function getMultiFirstCol($query_sql)
    {
        // TODO: Implement getMultiFirstCol() method.
    }

    /**
     * 返回一个key=>value格式的数组，第一个字段为key，第二个字段为value
     * @param string $query_sql SQL语句
     * @return array
     * @throws MysqlException
     */
    public function getMultiAssocCol($query_sql)
    {
        // TODO: Implement getMultiAssocCol() method.
    }

    /**
     * 执行一条SQL语句
     * @param string $query_sql
     * @return void
     * @throws MysqlException
     */
    public function query($query_sql)
    {
        // TODO: Implement query() method.
    }

    /**
     * 取得最后的last_insert_id
     * @return int
     */
    public function lastInsertId()
    {
        // TODO: Implement lastInsertId() method.
    }

    /**
     * 获取上一次执行影响的记录数
     * @return int
     */
    public function affectRows()
    {
        // TODO: Implement affectRows() method.
    }

    /**
     * 提交变更
     * @return void
     * @throws MysqlException
     */
    public function commit()
    {
        // TODO: Implement commit() method.
    }

    /**
     * 回滚
     * @return void
     * @throws MysqlException
     */
    public function rollback()
    {
        // TODO: Implement rollback() method.
    }

    /**
     * 是否与服务端保持连接
     * @return bool
     */
    public function ping()
    {
        // TODO: Implement ping() method.
    }

    /**
     * 关闭
     * @return void
     */
    public function close()
    {
        // TODO: Implement close() method.
    }
}