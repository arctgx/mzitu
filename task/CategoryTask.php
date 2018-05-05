<?php

use lib\TaskBase;

use dao\Category as CategoryDao;
use lib\Log;
use service\Category as CategoryService;
use service\Album as AlbumService;
use lib\MyCurl;

class CategoryTask extends TaskBase {

    // php -f webroot/cli.php Category getall
    public function getallAction() {
        $daoCategory = CategoryDao::getModel();
        $categoryList = $daoCategory->getAllCategory();
        // var_dump($ret);

        foreach ($categoryList as $oneCategory) {
            // 取首页
            Log::output(sprintf('process cateogory %s', $oneCategory['category']));
            $htmlContents = CategoryService::getPage($oneCategory['short_name']);
            // var_dump($html);
            if ($htmlContents===false) {
                Log::output('get html fail, catetory is '.$oneCategory['category']);
                continue;
            }
            $totalPage = CategoryService::getTotalPageNumber($htmlContents);
            if ($totalPage===false) {
                Log::output('get total number fail, catetory is '.$oneCategory['category']);
                continue;
            }
            $albumIDs = CategoryService::extractAlbumID($htmlContents);
            AlbumService::saveAlbumIDs($oneCategory['id'], $albumIDs);

            for ($i=2; $i<=$totalPage; ++$i) {
                $htmlContents = CategoryService::getPage($oneCategory['short_name'], $i);
                if ($totalPage===false) {
                    Log::output('get html fail, catetory is '.$oneCategory['category'] . ' page is '. $i);
                    continue;
                }
                $albumIDs = CategoryService::extractAlbumID($htmlContents);
                AlbumService::saveAlbumIDs($oneCategory['id'], $albumIDs);
            }
            // var_dump($totalPage);
            // exit();
        }
    }

    public function testAction() {
        // $url = 'http://www.mzitu.com/mm/';
        // $ret = MyCurl::dlHtml($url);
        // var_dump($ret);
        $ret = CategoryService::getPage('mm', 11);

    }
}
