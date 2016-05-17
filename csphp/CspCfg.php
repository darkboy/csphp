<?php
//系统配置文件
$systemConfig=array(
    'csphp_start_time'  =>microtime(true),
    //框架所在目录
    'system_base_path'  =>__DIR__,
    //是否开启调试
    'is_debug'          =>false,


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
    'log_base_path'    =>dirname(__DIR__).'/app/var/log',
    'log_stay_days'    =>7,
    'is_log_info'      =>true,
    'is_log_debug'     =>true,
    'is_log_warning'   =>true,
    'is_log_error'     =>true,

    //用户可以指定 获取ip的尝试顺序，注，在使用 反向代理后，一般不能使用 REMOTE_ADDR ，不同的服务器，可能设置为不同的KEY
    'ip_keys_order'     =>array(
        'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP',
        'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP', 'REMOTE_ADDR'),

    //


);



return $systemConfig;
