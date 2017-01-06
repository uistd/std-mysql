<?php
namespace ffan\php\mysql;

/**
 * Interface MysqlInterface
 * @package ffan\php\mysql
 */
interface MysqlInterface
{
    /**
     * 获取一条记录
     * @param string $query_sql SQL语句
     * @param null|string $class_name 对象名称，如果传入非NULL，会返回对象
     * @return array|object|null
     * @throws MysqlException
     */
    public function getRow($query_sql, $class_name = null);

    /**
     * 获取查询结果的第一条记录的第一个字段
     * @param $query_sql
     * @return string|null
     * @throws MysqlException
     */
    public function getFirstCol($query_sql);

    /**
     * 获取多条记录，以枚举数组返回
     * @param string $query_sql SQL语句
     * @param null|string $class_name 对象名称，如果传入非NULL，会返回对象
     * @return array|object
     * @throws MysqlException
     */
    public function getMultiRow($query_sql, $class_name = null);

    /**
     * 获取多条记录，以枚举数组返回
     * @param string $query_sql SQL语句
     * @param string $index_col 做为key的字段名
     * @param null|string $class_name
     * @return array|object
     * @throws MysqlException
     */
    public function getMultiAssocRow($query_sql, $index_col, $class_name = null);

    /**
     * 所有查询记录的第一个字段，以数组返回
     * @param string $query_sql SQL语句
     * @return array
     * @throws MysqlException
     */
    public function getMultiFirstCol($query_sql);

    /**
     * 返回一个key=>value格式的数组，第一个字段为key，第二个字段为value
     * @param string $query_sql SQL语句
     * @return array
     * @throws MysqlException
     */
    public function getMultiAssocCol($query_sql);
    
    /**
     * 执行一条SQL语句
     * @param string $query_sql
     * @return void
     * @throws MysqlException
     */
    public function query($query_sql);


    /**
     * 更新记录
     * @param string $table 表名
     * @param array $data 数据
     * @param string $condition 条件
     * @param int $limit 限制条数 0：不限制
     * @return int 影响条数
     * @throws MysqlException
     */
    public function update($table, array $data, $condition, $limit = 1);

    /**
     * 插入一条或者多条数据
     * @param string $table
     * @param array $data
     * @return void
     * @throws MysqlException
     */
    public function insert($table, array $data);

    /**
     * 删除记录
     * @param string $table 表名
     * @param string $condition 条件
     * @param int $limit 限制记录 0：不限制
     * @return int 影响条数
     * @throws MysqlException
     */
    public function delete($table, $condition, $limit = 1);
    
    /**
     * 取得最后的last_insert_id
     * @return int
     */
    public function lastInsertId();

    /**
     * 获取上一次执行影响的记录数
     * @return int
     */
    public function affectRows();
    
    /**
     * 提交变更
     * @return void
     * @throws MysqlException
     */
    public function commit();

    /**
     * 回滚
     * @return void
     * @throws MysqlException
     */
    public function rollback();

    /**
     * 是否与服务端保持连接
     * @return bool
     */
    public function ping();

    /**
     * 关闭
     * @return void
     */
    public function close();
}
