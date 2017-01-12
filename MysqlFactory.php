<?php
namespace ffan\php\mysql;

use ffan\php\utils\Config as FFanConfig;
use ffan\php\utils\InvalidConfigException;
use ffan\php\utils\Str as FFanStr;

/**
 * Class MysqlFactory
 * @package ffan\php\mysql
 */
class MysqlFactory
{
    /**
     * 配置名
     */
    const CONFIG_GROUP = 'ffan-mysql:';

    /**
     * @var array 对象池
     */
    private static $object_arr;

    /**
     * 配置名称
     * @param string $config_name
     * @return MysqlInterface
     * @throws InvalidConfigException
     */
    public static function get($config_name = 'main')
    {
        if (isset(self::$object_arr[$config_name])) {
            return self::$object_arr[$config_name];
        }
        if (!is_string($config_name)) {
            throw new \InvalidArgumentException('config_name is not string');
        }
        $conf_arr = FFanConfig::get(self::CONFIG_GROUP . $config_name);
        if (!is_array($conf_arr)) {
            $conf_arr = [];
        }
        //如果指定了日志的类名，使用指定的类
        if (isset($conf_arr['class_name'])) {
            $conf_key = self::CONFIG_GROUP . $config_name . '.class_name';
            if (!FFanStr::isValidClassName($conf_arr['class_name'])) {
                throw new InvalidConfigException($conf_key, 'invalid class name!');
            }
            $new_obj = new $conf_arr['class_name']($config_name, $conf_arr);
            if (!($new_obj instanceof MysqlInterface)) {
                throw new InvalidConfigException($conf_key, 'class is not implements of MysqlInterface');
            }
        } //如果配置里包含了master和slave, 自动使用RwMysql
        elseif (isset($conf_arr['master'], $conf_arr['slave'])) {
            $new_obj = new RwMysql($config_name, $conf_arr);
        } //其它情况，默认使用Mysql
        else {
            $new_obj = new Mysql($config_name, $conf_arr);
        }
        self::$object_arr[$config_name] = $new_obj;
        return $new_obj;
    }

    /**
     * 全部rollback
     */
    public static function rollback()
    {
        if (!self::$object_arr) {
            return;
        }

        /**
         * @var string $name
         * @var MysqlInterface $mysql_obj
         */
        foreach (self::$object_arr as $name => $mysql_obj) {
            $mysql_obj->rollback();
        }
    }

    /**
     * 全部commit
     */
    public static function commit()
    {
        if (!self::$object_arr) {
            return;
        }

        /**
         * @var string $name
         * @var MysqlInterface $mysql_obj
         */
        foreach (self::$object_arr as $name => $mysql_obj) {
            $mysql_obj->commit();
        }
    }
}
