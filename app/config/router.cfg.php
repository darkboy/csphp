<?php
namespace App;
//路由的配置示例
return array(

    'rpc_server'=>array(

        //请求过滤器，在什么条件下使用该路由规则 !filter 则表示反规则, 详见 CspRequest  过滤器  中的描述
        'filter'=>array(),
        //路由列表, 配置规则为： 路由模板=>目标路由
        'rule_list'=>array(
            //别名
            '/yar'           =>'@ctrl/rpc/yar/start',
        )
    ),

    //规则名称=>规则内容
    'demo_rule'=>array(

        //请求过滤器，在什么条件下使用该路由规则 !filter 则表示反规则, 详见 CspRequest  过滤器  中的描述
        'filter'=>array(
            //这条规则表示访问 /user/* 时启用这个组件
            //'match'=>"/user/*",
            //这条规则表示只在 domain.com 下使用
            'host'=>'*.csphp.com'
        ),

        //actionName::actionArg 如: del_suffix::.html
        'before_action'=>array(),

        //actionName::actionArg 如: add_prefix::/api
        'alfter_action'=>array(),

        //路由列表, 配置规则为： 路由模板=>目标路由
        'rule_list'=>array(
            //别名
            '/user/alias'           =>'/index',
            //闭包
            '/user/func'            =>function(){echo 'hello world';},
            //用户定义的 callable 结构，
            '/user/instance'        =>array('Csphp', 'obj'),
            //callable 绝对路由
            '/user/abs'             =>'@ext/account/info::action',
            '/user/abs2'            =>'\account\info::action',
            //变量与变量引用,如下规则将可实现迁移目的
            '/user/{arg1}/{arg2}'   =>'/account/{arg2}/{arg1}',
            //变量类型 和 长度 的限制
            '/user/{uid-d-2,5}'     =>'/account/view',
            //后缀规则,如下规则与 /user/* 相同，只是增加了变量引用
            '/user/{var-*}'         =>'/other/{var}',
            //通配符规则，无变量可用
            '/match/*'              =>'/match',
            //选择语法1，如下表示， 只有 /swyes/y1 和 /swyes/y2 会被匹配，例如 /swyes/y3 是不匹配的
            '/swyes/{vn-(y1|y2)}'   =>'/switch/catch_yes_sw1_sw2/{vn}',
            //选择语法2,跟上面相反，是除了 /swno/n1 和 /swno/n2 外 ，例如 /swno/n3 是匹配的
            '/swno/{vn-(?!n1|n2)}'  =>'/switch/catch_no_n1_n2/{vn}',
            //正则表达式规则，当使用正则表达式时，必须以 # 号开头，即以 # 作为正则分隔符，里面的 命名捕获将作为路由变量 如下面的ID
            '#^/regexp/(?<id>[\d]+)$#sim'    =>'/regexp/{id}'
        )
    ),

);