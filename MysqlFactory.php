<?php
namespace ffan\php\mysql;

use ffan\php\utils\Factory as FFanFactory;
use ffan\php\utils\InvalidConfigException;

/**
 * Class MysqlFactory
 * @package ffan\php\mysql
 */
class MysqlFactory extends FFanFactory
{
    /**
     * @var string 配置组名
     */
    protected static $config_group = 'ffan-mysql';

    /**
     * @var array 别名
     */
    protected static $class_alias = array(
        'RwMysql' => 'ffan\php\mysql\RwMysql',
    );

    /**
     * 获取一个缓存实例
     * @param string $config_name
     * @return MysqlInterface
     * @throws InvalidConfigException
     */
    public static function get($config_name = 'main')
    {
        $obj = self::getInstance($config_name);
        if (!($obj instanceof MysqlInterface)) {
            throw new InvalidConfigException(self::$config_group . ':' . $config_name . '.class', 'class is not implements of MysqlInterface');
        }
        return $obj;
    }

    /**
     * 默认的缓存类
     * @param string $config_name
     * @param array $conf_arr
     * @return MysqlInterface
     */
    protected static function defaultInstance($config_name, $conf_arr)
    {
        if ('rw' === $config_name) {
            return new RwMysql($config_name, $conf_arr);
        } else {
            return new Mysql($config_name, $conf_arr);
        }
    }
}
