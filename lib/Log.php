<?php

namespace lib;

class Log {

    protected static $_is_convert_to_gbk = true;

    protected static $_is_log_to_file = false;
    // protected static $_is_log_to_file = true;

    protected static $_log_file = 'task.log';

    public static function output($strLog) {
        if (self::$_is_convert_to_gbk) {
            $strLog = mb_convert_encoding($strLog, 'gbk');
        }
        if (self::$_is_log_to_file) {
            file_put_contents(LOG_PATH.self::$_log_file, $strLog."\n", FILE_APPEND);
        } else {
            printf("%s\n", $strLog);
            flush();
        }
    }

    public static function setLogFile($strLogFile) {
        self::$_log_file = $strLogFile;
    }

    public static function setIslogFile($boolIsLogFile) {
        self::$_is_log_to_file = $boolIsLogFile;
    }

}
