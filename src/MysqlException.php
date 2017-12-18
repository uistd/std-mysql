<?php
namespace UiStd\Mysql;

/**
 * Class MysqlException
 * @package UiStd\Mysql
 */
class MysqlException extends \Exception
{
    /**
     * 无法连接
     */
    const CONNECT_FAIL = 1;

    /**
     * 执行失败
     */
    const QUERY_FAIL = 2;

    /**
     * SQL语法错误
     */
    const QUERY_SYNTAX_ERROR = 3;

    /**
     * mysql断开
     */
    const MYSQL_GONE_AWAY = 2006;
}
