<?php
namespace App;
/**
 * 组件的配置说明:
 *
 *  组件 是包含一系列独立功能的类集合，如 DB CACHE ， Csphp 将所有的类封装都看作组件
 *
 *  组件配置提供的信息主要用于类的实例化, 包括如下信息
 *
 *  access_key          =>   访问别名, 可以用 Csphp::make($access_key) 获取实例
 *      class           =>   是什么类, 可以是一个类名路由，字符串，或者闭包
 *      options         =>   类选项, 类选项, Csphp 要求 组件有一个 初始化选项的成员方法 默认为 setInitOptions
 *      is_singleton    =>   是否是单例,默认为是
 *      option_method   =>   用于初始化选项的成员方法，默认为  setInitOptions
 *      pre_init        =>   是否预初始化，默认为 false,如果设置为 true 则提前生成实例，否则 在 调用 时再生成
 *
 */

return [

    //示例配置------
    //这个 key 供后续在应用中可以通过 Csphp::comp($access_key) 引用组件
    'comp_demo' => [
        //目标类，可以是类别名路由 类名 闭包
        'class'         => '@lib/libDemo',
        'pre_init'      => true,
        //是否预初始化，默认为 false,如果设置为 true 则提前生成实例，否则 在 调用 时再生成
        'is_singleton'  => true,
        // 用于初始化选项的成员方法，默认为  setInitOptions
        'option_method' => 'setInitOptions',
        //组件的配置选项字典列表，每个key将作来组件的属性被赋值
        'options'       => ['test'=>1]

    ],

    //-------------
    //这个 key 供后续在应用中可以通过 Csphp::comp($access_key) 引用组件
    'DB' => [
        //目标类，可以是类别名路由 类名 闭包
        'class'         => function(){
            return new \Csp\comp\db\CspCompDBConnection::getConnection('default');
        },
        'is_singleton'  => true,
        // 用于初始化选项的成员方法，默认为  setInitOptions
        'option_method' => 'setInitOptions',
        //组件的配置选项字典列表，每个key将作来组件的属性被赋值
        'options'       => []

    ],

    //-------------
    //这个 key 供后续在应用中可以通过 Csphp::comp($access_key) 引用组件
    'CACHE' => [
        //目标类，可以是类别名路由 类名 闭包
        'class'         => '@comp/demoComp',
        'is_singleton'  => true,
        // 用于初始化选项的成员方法，默认为  setInitOptions
        'option_method' => 'setInitOptions',
        //组件的配置选项字典列表，每个key将作来组件的属性被赋值
        'options'       => []

    ],

    //-------------
    //这个 key 供后续在应用中可以通过 Csphp::comp($access_key) 引用组件
    'REDIS' => [
        //目标类，可以是类别名路由 类名 闭包
        'class'         => '@comp/demoComp',
        'is_singleton'  => true,
        // 用于初始化选项的成员方法，默认为  setInitOptions
        'option_method' => 'setInitOptions',
        //组件的配置选项字典列表，每个key将作来组件的属性被赋值
        'options'       => []

    ],
    //-------------

];