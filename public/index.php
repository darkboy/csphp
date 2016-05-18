<?php
//phpinfo();exit;//for debug...

//设置时区
date_default_timezone_set("PRC");

//定义应用运行环境，和 调试开关 test prod dev
defined('CSPHP_ENV_TYPE') or define('CSPHP_ENV_TYPE', 'dev');
defined('CSPHP_IS_DEBUG') or define('CSPHP_IS_DEBUG', false);

//应用根目录
define('APP_ROOT_PATH', realpath(dirname(__FILE__).'/../'));

//加载 应用主配置文件
$appMainCfg = require(APP_ROOT_PATH.'/app/config/main.cfg.php');

//加载Autoload 和 框架主文件
require(APP_ROOT_PATH.'/csphp/CsphpAutoload.php');
require(APP_ROOT_PATH.'/csphp/Csphp.php');

//可以使用 xhporf 进行 性能分析，你也可以显式的调用使用 Csphp::xhporfEnd(); 结束 xhprof
//Csphp::xhprofEnable();

//创建并运行一个运用
Csphp::createApp($appMainCfg)->run();



