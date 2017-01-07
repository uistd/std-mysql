<?php
namespace ffan\php\mysql;

/**
 * Class MysqlCommon Mysql操作通用函数
 * @package ffan\php\mysql
 */
class MysqlCommon
{
    /**
     * 判断sql是否是写操作
     * @param string $sql SQL语句
     * @return bool
     */
    public static function isWriteSql($sql)
    {
        $type_str = strtoupper(substr($sql, 0, 6));
        //sql语句只要不是select，都是写操作
        return 'SELECT' !== $type_str;
    }
}