<?php
namespace lib;

class Config {

    protected static $_conf = array();

    protected static function _init() {
        if (empty(self::$_conf)) {
            self::$_conf = array(
                'db'    => include CONF_PATH.'db.php',
                'task'  => include CONF_PATH.'config.php',
                'redis' => include CONF_PATH.'redis.php',
            );
        }
    }

    public static function getDBConf() {
        self::_init();
        return self::$_conf['db'];
    }

    public static function getTaskConf() {
        self::_init();
        return self::$_conf['task'];
    }

    public static function getRedisConf() {
        self::_init();
        return self::$_conf['redis'];
    }

}
