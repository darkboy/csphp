<?php
/**
 * 系统将自动加载 helpers 中的 *.preload.php 文件 ，扫苗2层目录
 * 这个文件不会被加载
 */

function preload_function_demo2(){
    echo 'Hello: '.__FUNCTION__;
}