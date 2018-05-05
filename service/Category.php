<?php

namespace service;

use lib\MyCurl;
use lib\Log;

class Category {

    // 获取某页
    public static function getPage($categoryShortName, $pn=1) {
        $filePath = DATA_PATH.$categoryShortName.DIRECTORY_SEPARATOR.$pn.'.html';
        if (file_exists($filePath) && filesize($filePath)>0) {
            return file_get_contents($filePath);
        }

        $url = self::getPageUrl($categoryShortName, $pn);
        $html = MyCurl::dlHtml($url);
        if ($html === false) {
            return $html;
        }
        file_put_contents($filePath, $html);
        return $html;
    }

    // 获取下载url
    public static function getPageUrl($categoryShortName, $pn=1) {
        if ($pn==1) {
            $url = sprintf('http://www.mzitu.com/%s/', $categoryShortName);
        } else {
            $url = sprintf('http://www.mzitu.com/%s/page/%d/', $categoryShortName, $pn);

        }
        return $url;
    }

    // 找出总页数
    public static function getTotalPageNumber($htmlContents) {
        $startIdx = strrpos($htmlContents, "<a class='page-numbers'");
        if (false===$startIdx) {
            $strLog = sprintf('find [%s] fail', "<a class='page-numbers'");
            Log::output($strLog);
            return false;
        }

        $endIdx = strpos($htmlContents, 'next page-numbers', $startIdx);
        if (false===$endIdx) {
            $strLog = sprintf('find [endIdx] fail');
            Log::output($strLog);
            return false;
        }

        $contents = substr($htmlContents, $startIdx, $endIdx-$startIdx);

        $tmp = array();
        $ret = preg_match('/\/page\/([0-9]+)\//', $contents, $tmp);
        if (!$ret) {
            Log::output('get total page num fail['.$contents.']');
            return false;
        }
        return $tmp[1];
    }

    // 找出专辑
    public static function extractAlbumID($htmlContents) {
        $tag = '<ul id="pins">';
        $startIdx = strpos($htmlContents, $tag);
        if ($startIdx === false) {
            $strLog = sprintf('find tag [%s] fail', $tag);
            Log::output($strLog);
            return false;
        }

        $tag = '</ul>';
        $endIdx = strpos($htmlContents, $tag, $startIdx);
        if ($endIdx === false) {
            $strLog = sprintf('find tag [%s] fail', $tag);
            Log::output($strLog);
            return false;
        }

        $contents = substr($htmlContents, $startIdx, $endIdx - $startIdx);
        $tmp = array();
        $ret = preg_match_all('/http:\/\/www\.mzitu\.com\/([0-9]+)/', $contents, $tmp);
        // var_dump($contents);
        // Log::output($contents);
        if ($ret) {
            return array_values(array_unique($tmp[1]));
        } else {
            return array();
        }
    }

}
