<?php

use lib\TaskBase;

use dao\Category as CategoryDao;

class TestTask extends TaskBase
{
    public function testAction()
    {
        printf("hello world\n");

        $daoCategory = CategoryDao::getModel();
        $ret = $daoCategory->getAllCategory();
        var_dump($ret);
    }
}
