<?php
namespace App;
//组件的配置示例
return array(

    //示例配置------
    //这个 key 供后续在应用中可以通过 Csphp::comp($access_key) 引用组件
    'access_key'=>array(
        //类对象路由
        'class' =>'@comp/demoComp',
        //是否需要进行预 启动, 组件必须实现 start 方法
        'start' =>false,
        //请求过滤器，在什么条件下使用该组件 !filter 则不在过滤中使用组件, 详见 过滤器描述
        'filter'=>array(
            //这条规则表示访问 /user/* 时启用这个组件
            'match'=>"/user/*",
            //这条规则表示，只在本机使用
            'ip'=>"127.0.0.1,::1",
            //这条规则表示只在 domain.com 下使用
            'host'=>'*.domain.com'
        ),
        //组件的配置选项字典列表，每个key将作来组件的属性被赋值
        'options'=>array(

        )

    ),
    //-------------



);