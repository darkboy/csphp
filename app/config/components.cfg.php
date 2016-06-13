<?php
namespace App;
/**
 * 组件的配置说明:
 *
 *  组件 是包含一系列独立功能的类集合，如 DB CACHE ， Csphp 将所有的类 都看作组件
 *
 *  组件配置提供的信息主要用于类的实例化, 包括如下信息
 *
 *      is_init         =>   是否预初始化，默认为 false,如果设置为 true 则提前生成实例，否则 仅在 调用时 再生成
 *      is_singleton    =>   是否是单例,默认为 否
 *      class           =>   是什么类, 可以是一个类名路由，字符串，或者闭包 function ($ioc[,$arg...])
 *      alias           =>   组件访问别名，如果配置项使用字符串KEY，则可省略，可用 Csphp::comp($aliasName) 获取实例
 *      abstract        =>   此组件实现的抽象（接口）， 依赖这个抽象（接口）的组件将被注入此组件
 *      args            =>   传递给构造函数的参数，数据索引则为第几个参数，字符串索引 则表示变更名
 *                           如 [0=>1,'name'=>2] 表示第1个参数为 1，name 参数为 2，传递给对应类的构造函数
 *
 *      boot            =>   组件的启动逻辑，接收自身，和 容器，两个参数 function ($self, CspIocContainer $ioc){}
 *
 */
use Csphp;
use Csp\comp;
use Csp\core\CspIocContainer;

return [

    //示例配置------
    //这个 key 将作为 类别名 供后续在应用中可以通过 Csphp::comp($aliasName) 引用组件
    'demo_comp'=>[

        //组件的 抽象或者接口，应用中 使用此抽象接口的组件，会被自动注入这个组件
        'abstract'      => '',
        //组件的别名，访问组件的 Key
        'alias'         => '',
        //是否使用门面特性，默认为空即不使用，如果为布尔值true则使用 alias 名为门面名
        'facades'       => '',

        //组件的 实现类，可以是类别名路由 类名 闭包
        'class'         => '@lib/libDemo',
        //传递给组件类构造函数的参数，数据索引则为第几个参数（索引从0开始），字符串索引 则表示变更名
        'args'          => [],

        //是否预初始化，默认为 false,如果设置为 true 则提前生成实例，否则 在 调用 时再生成
        'is_init'       => true,
        //是否共享组件，即是否单例，共享的组件只生成一个实例，否则每次调用容器都重新初始化
        'is_share'      => true,

        //组件的启动逻辑，接收自身，和 容器，两个参数 function ($self, CspIocContainer $ioc){}
        'boot'          => function ($self, CspIocContainer $ioc){},

    ],

    //-------------
    'DB' => [
        'is_share'      => true,
        //使用数据库默认配置 生产数据库链接
        'class'         => function(){
            return \Csp\comp\db\CspCompDBConnection::getConnection();
        }
    ],

    //-------------
    //缓存 组件 MC FILE RDDIS
    'CACHE' => [

    ],

    //-------------
    //REDIS 存储
    'REDIS' => [
    ],

    //MONGO 存储
    'MONGO' => [
    ],


    //-------------
    //Image图片处理组件
    'IMAGE' => [
    ],

    //Http客户端组件，用于收发HTTP请求报文
    'HTTP' => [

    ],

    //Xhprof 调优组件
    'XHPROF' => [

    ],

    //邮件收发组件
    'MAILER' => [

    ],

    //这个 key 供后续在应用中可以通过 Csphp::comp($aliasName) 引用组件
    'HASH' => [

    ],

    //这个 key 供后续在应用中可以通过 Csphp::comp($aliasName) 引用组件
    'SECURITY' => [

    ],



];