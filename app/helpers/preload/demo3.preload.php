<?php
/**
 * 系统将自动加载 helpers 中的 *.preload.php 文件 ，扫苗2层目录
 *
 */

function preload_function_demo3(){
    echo 'Hello: '.__FUNCTION__;
}

class preloadDemoClass {
    function __construct() {
    }

    public function hello($name='csphp'){
        return "Hello ".$name;
    }

}