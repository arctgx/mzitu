<?php

use lib\TaskBase;
use lib\Log;
use service\Pic as PicService;


class PicTask extends TaskBase {

    // 下载
    public function dlAction() {
        Log::setIslogFile(true);
        Log::setLogFile('pic_dl.log');

        $lastID = 0;
        while (true) {
            $picList = PicService::getNotDownloadPics($lastID);
            if (empty($picList)) {
                break;
            }
            foreach ($picList as $onePic) {
                $lastID = $onePic['id'];

                $ret = PicService::dlPic($onePic);
                $strLog = sprintf('dl pic, id [%d] url[%s] ret[%s]', $onePic['id'], $onePic['url'], json_encode($ret));
                Log::output($strLog);
            }
        }

        $strLog = sprintf('process done, last id is %d', $lastID);
        Log::output($strLog);
    }

}
