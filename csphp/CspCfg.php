<?php
//系统配置文件
$systemConfig=array(
    'app_start_time'=>microtime(true),

    //是否开启调试
    'is_debug'      =>false,

    //请求性质判断的条件
    'jsonp_flag_vr' =>array('g:cspcallback', 'p:cspcallback'),
    'ajax_flag_vr'  =>array('g:_', 'p:_'),
    'api_flag_vr'   =>array('h:csp-api'),

    //日志相关
    'log_key_separator'=>'#####',
    'log_base_path'    =>__DIR__,
    'log_stay_days'    =>7,
    'is_log_info'      =>true,
    'is_log_debug'     =>true,
    'is_log_warning'   =>true,
    'is_log_error'     =>true,



);



return $systemConfig;
