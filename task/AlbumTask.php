<?php

use lib\TaskBase;

use lib\Log;
use service\Album as AlbumService;

class AlbumTask extends TaskBase {

    public function getAllAction() {
        Log::setIslogFile(true);
        Log::setLogFile('album_getall.log');

        $lastID = 0;
        while (true) {
            $albumList = AlbumService::getNotProcessdAlbums($lastID);
            if (empty($albumList)) {
                break;
            }
            foreach ($albumList as $oneAlbum) {
                $lastID = $oneAlbum['id'];

                $albumID = $oneAlbum['album_id'];
                $ret = AlbumService::extractInfo($albumID, $oneAlbum);
                $strLog = sprintf(
                    'update album info, id[%d] album_id[%d] ret[%s]',
                    $oneAlbum['id'], $oneAlbum['album_id'], json_encode($ret)
                );
                Log::output($strLog);
            }
        }

        $strLog = sprintf('process done, last id is %d', $lastID);
        Log::output($strLog);
    }

    public function getpicAction() {
        Log::setIslogFile(true);
        Log::setLogFile('album_getpic.log');

        $lastID = 0;
        while (true) {
            $albumList = AlbumService::getNotProcessdPicAlbums($lastID);
            if (empty($albumList)) {
                break;
            }
            foreach ($albumList as $oneAlbum) {
                $lastID = $oneAlbum['id'];

                $albumID = $oneAlbum['album_id'];
                $ret = AlbumService::extractPicInfo($oneAlbum);
            }
        }

        $strLog = sprintf('process done, last id is %d', $lastID);
        Log::output($strLog);
    }
}
