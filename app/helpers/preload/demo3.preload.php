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
        //注册事件
        Csphp::on(Csphp::EVENT_CORE_EXIT, array($this,'onExitApp'));
    }

    //事件监听 示例，在程序结束时 输出一行日志
    public function onExitApp(){
        Csphp::logInfo('preloadDemoClass on exit',null,'access');
    }

    public function hello($name='csphp'){
        return "Hello ".$name;
    }

}

$tmp = new preloadDemoClass();