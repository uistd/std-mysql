<?php
namespace ffan\php\mysql;

/**
 * Class MysqlException
 * @package ffan\php\mysql
 */
class MysqlException extends \Exception {
    /**
     * 无法连接
     */
    const CONNECT_FAIL = 1;
    
    /**
     * 执行失败
     */
    const QUERY_FAIL = 2;
    
    /**
     * mysql断开
     */
    const MYSQL_GONE_AWAY = 2006;
}
