<?php
namespace App;
//模块配置示例，模块一个功能特性的集合，
//如 一般的网站会有 api admin www cli 4大模块，每个模块可能有独立的域名，控制器 和 模板目录
//默认模块的 www, 配置格式为 模块名=>配置内容
return array(
    'cli'=>array(
        //模块名, 在 控制器 示图  和 静态资源 目录下 是以模块名 命名的相关资源，
        'name'   =>'cli',
        //模块识别过滤器，系统 在什么条件下 识别为正在访问该模块，通常是域名，或者URL前缀
        'filter'=>array(
            //这条规则表示只在 www.csphp.com 下使用该模块
            'requestType'=>'cli'
        ),

        'default_route' =>'help',
    ),

    //------ 示例配置 ------
    'home'=>array(
        //模块名，不能重复，模块名用于 访问控制 或者 过滤器, 建议与 key 一致
        'name'   =>'home',
        //是否默认模块
        'is_default'    => true,
        //模块识别过滤器，系统 在什么条件下 识别为正在访问该模块，通常是域名，或者URL前缀
        'filter'=>array(
            //这条规则表示只在 www.csphp.com 下使用该模块
            //'host'=>'www.csphp.com'
            'host'=>'*'//这条规则表示 任意域名
        ),
        //'host'          => '*',
        //默认的控制器，即首页
        'default_route' =>'index/home',


    )
);