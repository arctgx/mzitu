<?php

namespace lib;

class MyCurl {

    protected static $userAgent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36';

    // 抓取网页
    // 成功返回数据，失败返回 false
    public static function dlHtml($url) {

        $userAgent = self::$userAgent;

        $strLog = sprintf('trace : curl url [%s]', $url);
        Log::output($strLog);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        // test gzip
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        // curl_setopt($ch, CURLOPT_POST, 1 );
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
        $contents = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno != 0) {
            $strLog = sprintf('get html fail, url[%s]', $url);
            Log::output($strLog);
            return false;
        }

        return $contents;
    }

    // 下载文件
    public static function dlPic($url, $filePath, $refer, $retry=4) {
        if (file_exists($filePath) && filesize($filePath)>1024) {
            return true;
        }

        $timeOut = 8;
        $tryTimes = 0;
        while ($tryTimes<$retry) {
            if ($tryTimes>0) {
                $strLog = sprintf('retry %d, url %s', $tryTimes, $url);
                Log::output($strLog);
            }

            $ret = self::_dl_pic($url, $filePath, $refer, $timeOut);
            if ($ret) {
                return true;
            }
            $tryTimes++;
            $timeOut *= 2;
        }

        return false;
    }

    protected static function _dl_pic($url, $filePath, $refer, $timeOut) {
        $fh = fopen($filePath, 'w');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($ch, CURLOPT_REFERER, $refer);
        $ret = curl_exec($ch);

        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($ret==false) {
            $strLog = sprintf('curl fail, errno[%s] error[%s]', $errno, $error);
            Log::output($strLog);
            return false;
        }

        if ($httpCode!='200') {
            $strLog = sprintf('curl fail, http code [%s]', $httpCode);
            Log::output($strLog);
            return false;
        }

        return true;
    }
}
