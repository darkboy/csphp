<?php
namespace App;
//路由的配置示例
return array(

    //-------------
    //规则名称=>规则内容
    'rule_name'=>array(
        //请求过滤器，在什么条件下使用该路由规则 !filter 则表示反规则, 详见 CspRequest  过滤器  中的描述
        'filter'=>array(
            //这条规则表示访问 /user/* 时启用这个组件
            'match'=>"/user/*",
            //这条规则表示，只在本机使用
            'ip'=>"127.0.0.1,::1",
            //这条规则表示只在 domain.com 下使用
            'host'=>'*.domain.com'
        ),
        //actionName=>actionArg
        'before_action'=>array(),

        //actionName=>actionArg
        'alfter_action'=>array(),

        //路由列表
        'rule_list'=>array(
            //别名
            '/user/info'=>'/account/info',
            //类似迁移的需求，在目标路由中使用 路由变量名称
            '/atricle/{vname-s-1-y}'=>'/news/{vname}',
            '/atricle/{vname-s-+-y}'=>'@ext/className->actionName',
            '/atricle/{vname-s-*-y}'=>function(){},
            '/atricle/{vname-s-*-o}'=>function(){},

        )
    ),
    //-------------



);