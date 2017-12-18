<?php
namespace UiStd\Mysql;

use UiStd\Common\Env as UisEnv;

/**
 * Class MysqlCommon Mysql操作通用函数
 * @package UiStd\Mysql
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

    /**
     * 获取分表的名称
     * @param string $table_name 表名
     * @param int $hash_id 用于hash的ID
     * @param int $table_count 总表数量
     * @return string
     */
    public static function tableHash($table_name, $hash_id, $table_count = 16)
    {
        if ($table_count < 1) {
            throw new \InvalidArgumentException('table count error');
        }
        $sub_id = $hash_id % $table_count;
        return $table_name . '_' . $sub_id;
    }
}
