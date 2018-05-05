<?php

use lib\TaskBase;

use dao\Websites as WebsitesDao;

// 抓取工作
class SpiderTask extends TaskBase
{
    // 抓取
    public function doAction()
    {
        printf("hello world\n");

        $daoWebsites = WebsitesDao::getModel();
        $ret = $daoWebsites->getAllWeb();
        var_dump($ret);
    }
}
