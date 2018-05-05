<?php
namespace lib;

class RedisManager {

    protected static $rdx = null;

    public static function getRedis() {
        if (is_null(self::$rdx)) {
            return self::init();
        }
        return self::getRdx();
    }

    protected static function init() {
        $rdxConf = Config::getRedisConf();
        self::$rdx = new \Redis();
        $ret = self::$rdx->pconnect($rdxConf['host'], $rdxConf['port']);
        if (!$ret) {
            printf("redis connect fail\n");
            return false;
        }

        $ret = self::$rdx->auth($rdxConf['pwd']);
        if (!$ret) {
            printf("redis auth fail\n");
            return false;
        }

        $ret = self::$rdx->ping();
        if ($ret != '+PONG') {
            printf("ping fail\n");
            return false;
        }

        return self::$rdx;
    }

    protected static function getRdx() {
        if ($ret != '+PONG') {
            printf("ping fail\n");
            return false;
        }

        return self::$rdx;
    }
}
