<?php
/**
 * 系统将自动加载 helpers 中的 *.preload.php 文件 ，扫苗2层目录
 *
 */

function preload_function_demo1(){
    echo 'Hello: '.__FUNCTION__;
}