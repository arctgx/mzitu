<?php

namespace service;

use lib\Log;
use lib\MyCurl;
use dao\Pic as PicDao;

class Pic {

    // 获取没有下载过的图片数据
    public static function getNotDownloadPics($lastID) {
        $daoPic = PicDao::getModel();
        return $daoPic->getNotDownloadPics($lastID, 100);
    }

    // 返回值 成功 true 失败 false
    public static function dlPic($picInfo) {
        $extension = pathinfo($picInfo['url'], PATHINFO_EXTENSION);

        $fileName = self::_get_file_name($picInfo['rank'], $picInfo['url'], $extension);
        $dirPath = self::_get_file_path($picInfo['album_id']);
        if (!is_dir($dirPath)) {
            mkdir($dirPath);
        }

        $filePath = $dirPath . $fileName;

        $referer = self::_get_dl_pic_refer($picInfo['album_id'], $picInfo['rank']);
        $dlRet = MyCurl::dlPic($picInfo['url'], $filePath, $referer);
        if (!$dlRet) {
            return false;
        }

        // 更新数据
        $updateData = array(
            'file_name' => $fileName,
            'file_size' => filesize($filePath),
            'dl_status' => PicDao::DL_STATUS_DONE,
        );

        $daoPic = PicDao::getModel();
        $ret = $daoPic->updateInfo($picInfo['id'], $updateData);
        if ($ret===false) {
            return false;
        }
        return $ret;
    }


    // 生成文件名 rank_{rank}_substr(md5sum(url), 0, 8);
    protected static function _get_file_name($rank, $url, $extension) {
        return sprintf(
            'rank_%02d_%s.%s',
            $rank,
            substr(md5($url), 0, 8),
            $extension
        );
    }

    // 生成文件路径
    protected static function _get_file_path($albumID) {
        return DATA_PATH.$albumID.DIRECTORY_SEPARATOR;
    }

    // 生成refer
    protected static function _get_dl_pic_refer($albumID, $rank) {
        if ($rank==1) {
            return sprintf('http://www.mzitu.com/%s/', $albumID);
        } else {
            return sprintf('http://www.mzitu.com/%s/%s/', $albumID, $rank);
        }
    }
}
