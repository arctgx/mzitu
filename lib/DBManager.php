<?php
namespace lib;

class DBManager {

    protected static $db = null;

    public static function getDB() {
        if (is_null(self::$db)) {
            self::getPdo();
        }
        return self::$db;
    }

    protected static function getPdo() {
        $dbConf = Config::getDBConf();
        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $dbConf['host'], $dbConf['port'], $dbConf['db'], $dbConf['charset']
        );
        self::$db = new \pdo($dsn, $dbConf['user'], $dbConf['pwd']);
        self::$db->query('set names utf8mb4');
    }
}
