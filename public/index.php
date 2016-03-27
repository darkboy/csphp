<?php

define('APP_ROOT_PATH', realpath(dirname(__FILE__).'/../'));
//加载 应用主配置文件
$appMainCfg = require(APP_ROOT_PATH.'/app/config/main.cfg.php');
//加载框架文件
require(APP_ROOT_PATH.'/csphp/Csphp.php');

//定义应用运行环境，和 调试开关
defined('CSPHP_ENV_TYPE') or define('CSPHP_ENV_TYPE', Csphp::ENV_TYPE_PROD);
defined('CSPHP_IS_DEBUG') or define('CSPHP_IS_DEBUG', false);

//创建并运行一个运用
Csphp::createApp($appMainCfg)->run();




