<?php

function my_auto_load($className) {

    $pos = strpos($className, '\\');
    if ($pos === false || $pos == 0) {
        return ;
    }
    $classFile = ROOT_PATH.str_replace('\\', DIRECTORY_SEPARATOR, $className).'.php';
    require_once $classFile;
    return ;
}
