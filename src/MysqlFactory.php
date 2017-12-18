<?php
namespace UiStd\Mysql;

use UiStd\Common\Factory as UisFactory;
use UiStd\Common\InvalidConfigException;

/**
 * Class MysqlFactory
 * @package UiStd\Mysql
 */
class MysqlFactory extends UisFactory
{
    /**
     * @var string 配置组名
     */
    protected static $config_group = 'uis-mysql';

    /**
     * @var array 别名
     */
    protected static $class_alias = array(
        'RwMysql' => 'UiStd\Mysql\RwMysql',
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
        $type = isset($conf_arr['type']) ? $conf_arr['type'] : '';
        if ('rw' === $type) {
            return new RwMysql($config_name, $conf_arr);
        } else {
            return new Mysql($config_name, $conf_arr);
        }
    }
}
