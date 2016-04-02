<?php
namespace App;
//模块配置示例，模块一个功能特性的集合，
//如 一般的网站会有 api admin www cli 4大模块，每个模块可能有独立的域名，控制器 和 模板目录
//默认模块的 www
return array(
    'cli'=>array(
        //模块识别过滤器，系统 在什么条件下 识别为正在访问该模块，通常是域名，或者URL前缀
        'filter'=>array(
            //这条规则表示只在 www.csphp.com 下使用该模块
            'requestType'=>'cli'
        ),
        //模块名
        'module_name'   =>'cli',
        'default_route' =>'help',
        //控制器目录，基准目录为 @ctrl
        'ctrl_base'    =>'home',
        //示图，模板目录
        'view_base'    =>'home',

        'router_list'   =>array(

        )
    ),

    //------ 示例配置, 建议将默认模块 放在最后面 将过滤器设置为空 ------
    'www'=>array(
        //模块识别过滤器，系统 在什么条件下 识别为正在访问该模块，通常是域名，或者URL前缀
        'filter'=>array(
            //这条规则表示只在 www.csphp.com 下使用该模块
            'host'=>'www.csphp.com'
        ),
        //模块名
        'module_name'   =>'www',
        'default_route' =>'index',
        //控制器目录，基准目录为 @ctrl
        'ctrl_base'    =>'home',
        //示图，模板目录
        'view_base'    =>'home',

        'router_list'   =>array(

        )


    )
);