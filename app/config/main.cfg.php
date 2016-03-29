<?php
namespace App;
//配置示例
$appConfig = array(
    //应用名称
    'app_name'      =>'demo app',
    //应用的版本号 规则为 Ymd.no.svnno
    'app_version'   =>'20160301.01.xxxx',

    //应用的根目录
    'app_base_path' =>dirname(__DIR__),
    //应用配置目录
    'app_cfg_path'  =>__DIR__,
    //应用命名空间
    'app_namespace' =>__NAMESPACE__,

    //在框架引导期间 会自动加载的目录或者文件，文件或者目录为key 值为加载的目录深度,如果加载目录为文件则后面的值无效，始终为 0
    'auto_include_path'=>array(
        //fileOrDir=>level
    ),

    //路径别名配置
    'alias_path_config'=>array(
        //@aliasname=>array(path-实际路径,nsPrefix-命名空间不需要时置空)
        '@demo'=>array('@cfg/demo','')
    ),

    //应用所用的host url 前缀，用于组装URL，拼接静态文件，等
    'host_key'=>array(
        '_default'	=>'http://www.domain.com/',
        'home'		=>'http://www.domain.com/',
        'admin'		=>'http://admin.domain.com/',
        'statics'	=>'http://admin.domain.com/',
        'api'		=>'http://api.domain.com/',

    ),
    //主页
    'home_url'      =>'/',


    //配置中引用配置
    'demo_for_file'=>array(
        '@myext'=>'-:mysql'
    ),







);



//可以重写覆盖系统配置
$systemCfg = array(

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

//以下配置将会 覆盖 系统配置
$appConfig['system_config_over_write'] = $systemCfg;
return $appConfig;

