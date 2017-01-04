<?php
namespace ffan\php\mysql;

/**
 * Interface QueryInterface 生成SQL语句的接口
 * @package ffan\php\mysql
 */
interface QueryInterface{
    /**
     * QueryInterface constructor.
     * @param string $table_name 操作的表名
     * @param string $config_name 配置名（根据配置自动实现分表等功能）
     */
    public function __construct($table_name, $config_name = 'main');

    /**
     * 增加where条件
     * @param string $where_str
     * @param int $logic 条件类型 默认是 AND
     */
    public function where($where_str, $logic = 0);

    /**
     * sql语句的order部分
     * @param string $order_fields 排序字段
     * @param int $order_type 排序类型
     * @return void
     */
    public function order($order_fields, $order_type = 0);

    /**
     * sql语句的group部分
     * @param string $group_str
     * @return void
     */
    public function group($group_str);
    
    /**
     * 设置SQL语句的limit部分
     * @param int $start_row
     * @param int $limit
     * @return void
     */
    public function limit($start_row = 0, $limit = 1);
    
    /**
     * 生成查询SQL语句
     * @param string $fields
     * @return string
     */
    public function select( $fields = '*' );

    /**
     * 生成更新SQL语句
     * @param array $update 更新的数据
     * @param int $limit 限制条数 默认是1条，0：表示不限制
     * @return string
     */
    public function update($update, $limit = 1);

    /**
     * 生成删除记录的SQL语句
     * @param int $limit 限制条数 0 表示不限制
     * @return string
     */
    public function delete($limit = 1);

    /**
     * 生成插入的SQL语句
     * @param array $data
     * @return string
     */
    public function insert($data);
}
