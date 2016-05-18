<?php
namespace App\config;
//配置示例
$appConfig = array(
    //应用名称
    'app_name'      =>'demoApp',

    //应用的版本号 规则为 Ymd.no.svnno
    'app_version'   =>'20160301.01.xxxx',

    //应用的根目录
    'app_base_path' =>dirname(__DIR__),
    //应用配置目录
    'app_cfg_path'  =>__DIR__,
    //应用命名空间
    'app_namespace' =>'App',

    //用于加解密的KEY
    'app_secret_key'=>'Kals266kAd;@s2E30#Sdlk9,a.Ke',

    //路径别名 自定义配置
    'alias_path_config'=>array(
        //@aliasname=>array(path-实际路径,nsPrefix-命名空间不需要时置空)
        '@demo'=>array('@cfg/demo','')
    ),

    //应用 的一些 url 前缀，用于组装URL，拼接静态文件，等
    'urls'=>array(
        '_default'	=>'http://www.domain.com/',
        'home'		=>'http://www.domain.com/',
        'admin'		=>'http://admin.domain.com/',
        'api'		=>'http://api.domain.com/',
        'statics'	=>'http://statics.domain.com/statics/',

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

    //-------------------------------------------------------------------------
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

    //-------------------------------------------------------------------------
    //日志相关的配置
    'log_key_separator'=>'#####',
    'log_base_path'    =>dirname(__DIR__).'/var/log',
    'log_stay_days'    =>7,
    'is_log_info'      =>true,
    'is_log_debug'     =>true,
    'is_log_warning'   =>true,
    'is_log_error'     =>true,
    //-------------------------------------------------------------------------


);

//以下配置将会 覆盖 系统配置
$appConfig['system_config_over_write'] = $systemCfg;
return $appConfig;

