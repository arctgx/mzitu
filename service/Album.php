<?php

namespace service;

use lib\Log;
use lib\MyCurl;
use dao\Album as AlbumDao;
use dao\Pic as PicDao;

class Album {

    public static function getNotProcessdAlbums($lastID) {
        $daoAlbum = AlbumDao::getModel();
        return $daoAlbum->getNotProcessd($lastID, 100);
    }

    public static function getNotProcessdPicAlbums($lastID) {
        $daoAlbum = AlbumDao::getModel();
        return $daoAlbum->getNotProcessdPicAlbums($lastID, 100);
    }

    public static function getNewestPage($pn) {
        $url = self::getNewPageUrl($pn);
        $htmlContents = MyCurl::dlHtml($url);
        if (false===$htmlContents) {
            $strLog = sprintf('get newset page fail, pn [%d]', $pn);
            Log::output($strLog);
            return false;
        }
        $albumIDList = self::extrctNewestAlbumInfo($htmlContents);
        if ($albumIDList===false) {
            $strLog = sprintf('get album_id list fail, pn [%d]', $pn);
            Log::output($strLog);
            return false;
        }

        self::saveAlbumIDs(0, $albumIDList);
    }

    public static function extractInfo($albumID, $albumInfo) {
        $daoAlbum = AlbumDao::getModel();


        $url = self::getUrl($albumID, 1);
        $htmlContents = MyCurl::dlHtml($url);
        // var_dump($htmlContents);
        if (false===$htmlContents) {
            $strLog = sprintf('get html fail, album_id[%d] pn [%d]', $albumID, 1);
            Log::output($strLog);
            return false;
        }

        $extractedAlbumInfo = self::extractAlbumInfo($albumID, $htmlContents);
        if ($extractedAlbumInfo === false) {
            return false;
        }

        $updateData = array(
            'title'       => $extractedAlbumInfo['title'],
            'total_pic'   => $extractedAlbumInfo['total_pic'],
            'create_at'   => $extractedAlbumInfo['create_at'],
            'info_status' => AlbumDao::I_STAT_DONE,
        );
        $ret = $daoAlbum->updateInfo($albumInfo['id'], $updateData);
        return $ret;
    }

    // 提取所有图片
    public static function extractPicInfo($albumInfo) {
        $daoPic = PicDao::getModel();
        $daoAlbum = AlbumDao::getModel();

        $total = 0;

        $albumID = $albumInfo['album_id'];
        $totalPic = $albumInfo['total_pic'];
        for($i=1; $i<=$totalPic; ++$i) {
            $picInfo = $daoPic->getByAlbumRank($albumID, $i);
            if (!empty($picInfo)) {
                $strLog = sprintf('pic exists, album_id[%d] rank[%d]', $albumID, $i);
                Log::output($strLog);
                $total++;
                continue;
            }
            $url = self::getUrl($albumID, $i);

            $htmlContents = MyCurl::dlHtml($url);
            if ($htmlContents === false) {
                $strLog = sprintf('get pic html fail, album_id[%d] rank[%d]', $albumID, $i);
                Log::output($strLog);
                continue;
            }

            $url = self::extractPicUrl($htmlContents);
            if (false === $url) {
                $strLog = sprintf('get pic url fail, album_id[%d] rank[%d]', $albumID, $i);
                Log::output($strLog);
                continue;
            }

            $addData = array(
                'album_id'    => $albumID,
                'rank'        => $i,
                'url'         => $url,
            );

            $ret = $daoPic->addItem($addData);
            $strLog = sprintf('save pic info, album_id[%d] rank[%d] ret[%s]', $albumID, $i, json_encode($ret));
            Log::output($strLog);

            if ($ret !== false) {
                $total++;
            }
        }

        if ($total == $totalPic) {
            $updateData = array(
                'process_status' => AlbumDao::P_STAT_DONE,
            );
            $ret = $daoAlbum->updateInfo($albumInfo['id'], $updateData);
            $strLog = sprintf("update album processed, album_id [%d] ret [%s]", $albumID, json_encode($ret));
            Log::output($strLog);
        }
    }

    // 将所有id先保存到数据库
    public static function saveAlbumIDs($categoryID, $albumIDList) {
        if (!is_array($albumIDList) || empty($albumIDList)) {
            return ;
        }

        $daoAlbum = AlbumDao::getModel();
        foreach ($albumIDList as $oneAlbumID) {
            $albumInfo = $daoAlbum->getByAlbumID($oneAlbumID);
            if (!empty($albumInfo)) {
                $strLog = sprintf('album exists, album_id[%s]', $oneAlbumID);
                Log::output($strLog);
                continue;
            }

            $data = array(
                'album_id'    => $oneAlbumID,
                'category_id' => $categoryID,
            );
            $ret = $daoAlbum->addItem($data);
            if ($data===false) {
                $strLog = sprintf('add album fail, album_id[%s]', $oneAlbumID);
                Log::output($strLog);
                continue;
            } else {
                $strLog = sprintf('add album success, album_id[%s] insert id [%s]', $oneAlbumID, $ret);
                Log::output($strLog);
            }
        }
    }

    protected static function extractPicUrl($htmlContents) {
        $tag = '<div class="main-image">';
        $startIdx = strpos($htmlContents, $tag);
        if (false === $startIdx) {
            $strLog = sprintf("find tag [%s] fail", $tag);
            Log::output($strLog);
            return false;
        }

        $tag = '</div>';
        $endIdx = strpos($htmlContents, $tag, $startIdx);
        if (false === $endIdx) {
            $strLog = sprintf("find tag [%s] fail", $tag);
            Log::output($strLog);
            return false;
        }

        $contents = substr($htmlContents, $startIdx, $endIdx - $startIdx);

        $tmp = array();
        $matchRet = preg_match('/img src=[\'"]([^\'"]+)[\'"]/', $contents, $tmp);
        if (!$matchRet) {
            return false;
        }
        return $tmp[1];
    }

    protected static function getUrl($albumID, $pn) {
        if ($pn==1) {
            $url = sprintf('http://www.mzitu.com/%s', $albumID);
        } else {
            $url = sprintf('http://www.mzitu.com/%s/%s', $albumID, $pn);
        }
        return $url;
    }

    protected static function getNewPageUrl($pn) {
        if ($pn==1) {
            $url = 'http://www.mzitu.com/';
        } else {
            $url = sprintf('http://www.mzitu.com/page/%d/', $pn);
        }
        return $url;
    }

    protected static $_category_map = array(
        '性感妹子' => 1,
        '日本妹子' => 2,
        '台湾妹子' => 3,
        '清纯妹子' => 4,
    );

    protected static function extractAlbumInfo($albumID, $htmlContents) {
        $categoryID = self::extractCategory($albumID, $htmlContents);

        $title = self::extractTitle($htmlContents);
        if ($title===false) {
            $strLog = sprintf('get album title fail, album_id[%d]', $albumID);
            Log::output($strLog);
            return false;
        }

        $ctTime = self::extractCttime($htmlContents);
        if ($title===false) {
            $strLog = sprintf('get album cttime fail, album_id[%d]', $albumID);
            Log::output($strLog);
            return false;
        }

        $totalPic = self::extractTotalPic($albumID, $htmlContents);
        if ($totalPic===false) {
            $strLog = sprintf('get album totalPic fail, album_id[%d]', $albumID);
            Log::output($strLog);
            return false;
        }

        return array(
            'category_id' => $categoryID,
            'title'       => $title,
            'total_pic'   => $totalPic,
            'create_at'   => strtotime($ctTime),
        );
    }

    protected static function extractCategory($albumID, $htmlContents) {
        $categoryID = 0;

        $tmp = array();
        $matchRet = preg_match('/category tag">([^<]+)<\/a>/', $htmlContents, $tmp);
        if ($matchRet) {
            $categoryName = $tmp[1];

            if (isset(self::$_category_map[$categoryName])) {
                $categoryID = self::$_category_map[$categoryName];
            } else {
                $strLog = sprintf('unknown category [%s]', $categoryName);
                Log::output($strLog);
            }
        } else {
            $strLog = sprintf('extrac category fail, album id[%d]', $albumID);
            Log::output($strLog);
        }
        return $categoryID;
    }

    protected static function extractTitle($htmlContents) {
        $tmp = array();
        $matchRet = preg_match('/<h2 class="main-title">([^<]+)<\/h2>/', $htmlContents, $tmp);
        if ($matchRet) {
            return $tmp[1];
        }
        return false;
    }

    protected static function extractCttime($htmlContents) {
        $tmp = array();
        $matchRet = preg_match('/<span>发布于 ([0-9: -]+)<\/span>/', $htmlContents, $tmp);
        if ($matchRet) {
            return $tmp[1];
        }
        return false;
    }

    protected static function extractTotalPic($albumID, $htmlContents) {
        $tmp = array();
        $patten = '/http:\/\/www\.mzitu\.com\/'.$albumID.'\/([0-9]+)["\']/';
        $matchRet = preg_match_all($patten, $htmlContents, $tmp);

        if (!$matchRet) {
            return false;
        }
        $totalPic = -1;
        foreach ($tmp[1] as $v) {
            if ($totalPic<$v) {
                $totalPic = $v;
            }
        }
        if ($totalPic == -1) {
            return false;
        }
        return $totalPic;
    }

    protected static function extrctNewestAlbumInfo($htmlContents) {
        $tag = '<ul id="pins">';
        $startIdx = strpos($htmlContents, $tag);
        if ($tag === false) {
            $strLog = sprintf('find tag[%s] fail', $tag);
            Log::output($strLog);
            return false;
        }

        $tag = '</ul>';
        $endIdx = strpos($htmlContents, $tag, $startIdx);
        if ($tag === false) {
            $strLog = sprintf('find tag[%s] fail', $tag);
            Log::output($strLog);
            return false;
        }
        $contents = substr($htmlContents, $startIdx, $endIdx - $startIdx);

        $tmp = array();
        $ret = preg_match_all('/http:\/\/www\.mzitu\.com\/([0-9]+)/', $contents, $tmp);
        // var_dump($ret);
        // var_dump($tmp);
        if ($ret) {
            return array_unique($tmp[1]);
        } else {
            return array();
        }
    }
}
