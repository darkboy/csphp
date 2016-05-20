<?php
//phpinfo();exit;//for debug...

//设置时区
date_default_timezone_set("PRC");

//定义入口名称
defined('CSPHP_ENTRYNAME')  or define('CSPHP_ENTRYNAME', 'index');
//定义应用运行环境，和 调试开关 test prod dev
defined('CSPHP_ENV_TYPE')   or define('CSPHP_ENV_TYPE', 'dev');
defined('CSPHP_IS_DEBUG')   or define('CSPHP_IS_DEBUG', false);

//项目目录 常量 不包含 右 /
define('CSPHP_PROJECT_ROOT', dirname(__DIR__));

//加载 应用主配置文件
$appMainCfg = require(CSPHP_PROJECT_ROOT.'/app/config/main.cfg.php');

//加载 框架引导文件
require(CSPHP_PROJECT_ROOT.'/csphp/Startup.php');

//可以使用 xhporf 进行 性能分析，你也可以显式的调用使用 Csphp::xhporfEnd(); 结束 xhprof 来分析特定区块
Csphp::xhprofEnable(false);

//使用配置信息，创建并运行一个运用
Csphp::createApp($appMainCfg)->run();



