<?php
/**
 * 放在 app/helpers/ 目录中，并且 以 .preload.php 命名的php文件 将会被预加载
 *
 */

function preload_golbal_function_demo2(){
    echo 'Hello: '.__FUNCTION__;
}