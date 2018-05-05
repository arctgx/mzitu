<?php

date_default_timezone_set('Asia/Shanghai');

if (PHP_OS == 'WINNT') {
    define('SYS', 'WIN');
} else {
    define('SYS', 'LINUX');
}

define('RUN_START_TIME', time());

// 定时路径

$webRootPath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$rootPath    = realpath($webRootPath.'..') . DIRECTORY_SEPARATOR;

define('ROOT_PATH', $rootPath);
define('LIB_PATH',  ROOT_PATH . 'lib'  . DIRECTORY_SEPARATOR);
define('CONF_PATH', ROOT_PATH . 'conf' . DIRECTORY_SEPARATOR);
define('LOG_PATH',  ROOT_PATH . 'log'  . DIRECTORY_SEPARATOR);
define('TASK_PATH', ROOT_PATH . 'task' . DIRECTORY_SEPARATOR);
define('DATA_PATH', ROOT_PATH . 'data' . DIRECTORY_SEPARATOR);


function useage() {
    printf("php -f cli.php class_name method_name\n");
}

function getParams($cliParams, $skipNum=3) {
    $retParams = array();
    for($i=0; $i<$skipNum && !empty($cliParams); ++$i) {
        array_shift($cliParams);
    }
    if (!empty($cliParams)) {
        foreach ($cliParams as $one) {
            if (false === ($idx = strpos($one, '='))) {
                continue;
            }
            $key = substr($one, 0, $idx);
            $val = substr($one, $idx + 1);
            $retParams[$key] = $val;
        }
    }
    return $retParams;
}

require_once 'autoload.php';
spl_autoload_register('my_auto_load', true, true);

if ($_SERVER['argc'] < 3) {
    useage();
    exit(0);
}


$className  = $_SERVER['argv'][1].'Task';
$methodName = $_SERVER['argv'][2].'Action';

$classFile = TASK_PATH . $className.'.php';
if (!file_exists($classFile)) {
    printf("class file[%s] not found\n", $classFile);
    useage();
    exit(0);
}

require_once $classFile;

if (!class_exists($className)) {
    printf("invalid class\n");
    useage();
    exit(0);
}


$params = getParams($_SERVER['argv']);
// var_dump($_SERVER['argv']); var_dump($params);exit();

$cliTask = new $className($params);
if (!method_exists($cliTask, $methodName)) {
    printf("invalid method\n");
    useage();
    exit(0);
}


// 运行程序
try {
    $cliTask->$methodName();
} catch (Exception $e) {
    printf("error %s\n", $e->getMessage());
}
