<?php
namespace App;
use Csp\core\CspRequest;
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
            //这条规则表示只在 在非 api. 开头的域名 下使用 此规则集，
            '!host'=>'api.*'
        ),

        //actionName::actionArg 如: del_suffix::.html
        'before_action'=>array(),

        //actionName::actionArg 如: add_prefix::/api
        'alfter_action'=>array(),

        //路由列表, 配置规则为： 路由模板=>目标路由
        'rule_list'=>array(
            //别名
            '/route_demo/alias'                      => '/demo/forRouteDemo',

            //闭包路由: function (CspRequest $request){}
            '/route_demo/func'                       => function (CspRequest $request) {

                echo 'Closure route , hello world, get[var]: '.json_encode($request->get('var'));

            },

            //对象路由, 指定路由对象
            '/route_demo/abs/1'                      => '@m-ctrl/demo::actionForRouteDemo',
            '/route_demo/abs/2'                      => '\App\comtrolers\home\demo::actionForRouteDemo',
            '/route_demo/abs/3'                      => 'demo::actionForRouteDemo',

            //变量与变量引用,如下规则将可实现迁移目的
            '/route_demo/var/{arg1}/{arg2}'          => '/demo/forRouteDemo',
            //变量类型 和 长度 的限制
            '/route_demo/id/{uid-d-2,5}'             => '/demo/forRouteDemo',

            //后缀规则,如下规则与 match/* 相同，只是增加了变量引用
            '/route_demo/any/{var-*}'                => '/demo/forRouteDemo',
            //通配符规则，无变量可用
            '/route_demo/match/*'                    => '/demo/forRouteDemo',

            //选择语法1，如下表示， 只有 swyes/y1 和 swyes/y2 会被匹配，例如 swyes/y3 是不匹配的
            '/route_demo/swyes/{vn-(y1|y2)}'         => '/demo/forRouteDemo',
            //选择语法2,跟上面相反，是除了 swno/n1 和 swno/n2 外 ，例如 swno/n3 是匹配的
            '/route_demo/swno/{vn-(?!n1|n2)}'        => '/demo/forRouteDemo',

            //正则表达式规则，当使用正则表达式时，必须以 # 号开头，即以 # 作为正则分隔符，里面的 命名捕获将作为路由变量 如下面的ID
            '#^/route_demo/regexp/(?<id>[\d]+)$#sim' => '/demo/forRouteDemo'
        )
    ),

);