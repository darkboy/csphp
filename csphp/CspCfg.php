<?php
//系统配置文件
$systemConfig=array(
    'app_start_time'    =>microtime(true),
    //框架所在目录
    'system_base_path'  =>__DIR__,

    //是否开启调试
    'is_debug'      =>false,


    //请求类型的判断条件 配置项 是一个 $requestFilter 过滤器条件
    'is_jsonp_req' =>array(
        //输入过滤器，有一个输入即通过
        'inputOne'=>array(
            array('g:cspCallback'),
            array('p:cspCallback'),
        )
    ),
    'is_ajax_req'  =>array(
        //输入过滤器，有一个输入即通过
        'inputOne'=>array(
            array('S:HTTP_X_REQUESTED_WITH', 'XMLHttpRequest', 'ci'),
        )
    ),
    'is_api_req'  =>array(
        //输入过滤器，有一个输入即通过
        'inputOne'=>array(
            array('H:csphp-api', 'csphp', 'ci'),
        )
    ),


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
