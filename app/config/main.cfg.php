<?php
//配置示例
$appConfig = array(
    'demo_key'=>array(
        'demo_key1'=>time()
    ),



);



//可以重写覆盖系统配置
$systemCfg = array(
    'jsonp_flag_vr' =>array('g:cspcallback', 'p:cspcallback'),
    'ajax_flag_vr'  =>array('g:_', 'p:_'),
    'api_flag_vr'   =>array('h:csp-api'),
);

//以下配置将会 覆盖 系统配置
$appConfig['system_config_over_write'] = $systemCfg;
return $appConfig;
