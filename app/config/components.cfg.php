<?php
namespace App;
/**
 * 组件的配置:
 *  组件 是包含一系列独立功能的类集合，如 DB CACHE ， Csphp 将所有的封装都看作组件
 *  组件配置 解决的是 类的实例化方式, 包括如下信息
 *      access_key      =>   访问别名, 可以用 Csphp::comp($access_key) 获取实例
 *      class           =>   是什么类, 可以是一个类名路由，字符串，或者闭包
 *      options         =>   类选项, 类选项, Csphp 要求 组件有一个 初始化选项的成员方法 默认为 setInitOptions
 *      is_singleton    =>   是否是单例,默认为是
 *      option_method   =>   用于初始化选项的成员方法，默认为  setInitOptions
 *
 */

return array(

    //示例配置------
    //这个 key 供后续在应用中可以通过 Csphp::comp($access_key) 引用组件
    'access_key'=>array(
        //目标类，可以是类别名路由 类名 闭包
        'class' =>'@comp/demoComp',
        //是否 是中间件
        'is_middle_ware' =>false,
        //组件的配置选项字典列表，每个key将作来组件的属性被赋值
        'options'=>array(
        )

    ),
    //-------------

    //这个 key 供后续在应用中可以通过 Csphp::comp($access_key) 引用组件
    'DB'=>array(
        //类对象路由
        'class' =>'@comp/demoComp',
        //组件的配置选项字典列表，每个key将作来组件的属性被赋值
        'options'=>array(

        )

    ),
    //-------------

    //这个 key 供后续在应用中可以通过 Csphp::comp($access_key) 引用组件
    'CACHE'=>array(
        //类对象路由
        'class' =>'@comp/demoComp',
        //组件的配置选项字典列表，每个key将作来组件的属性被赋值
        'options'=>array(

        )

    ),
    //-------------





);