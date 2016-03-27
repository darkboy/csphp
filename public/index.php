<?php

define('APP_ROOT_PATH', realpath(dirname(__FILE__).'/../'));

$appMainCfg = require(APP_ROOT_PATH.'/app/config/main.cfg.php');
require(APP_ROOT_PATH.'/csphp/Csphp.php');

Csphp::createApp($appMainCfg)->run();




