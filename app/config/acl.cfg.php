<?php
namespace App;
/**
 * 访问控制配置，deny 与 allow 的定义顺序会影响逻辑
 *  acl_name                    配置名称
 *      target,deny,allow
 *      target,allow,deny
 *
 *      三项配置都为 请求过滤器
 *      target  表示被检查的目标请求集，即对什么请求进行控制
 *      deny    表示禁止访问的条件,相当于黑名单，
 *      allow   表示允许访问的条件,相当于白名单
 *
 *  其它预载 helper 或者 comp 组件 可以通过
 *
 *      Csphp::useAccessControl($acl); 动态注册
 *
 *  在任意地方可以直接使用如下的两个方法，显式的 禁止 或者 允许 某些请求
 *      Csphp::deny($filter);
 *      Csphp::allow($filter);
 *
 *  在控制器 Acion 中，则可以使用进行控制
 *
 *      $this->deny($filter);
 *      $this->allow($filter);
 *
 */
return [
    //这条规则表示 /demo/aclDeny3 只请允许 127.0.0.1 访问
    'acl_name' => [
        'target' => [
            'match'=>'/demo/aclDeny3'
        ],
        'allow'  =>['ip'=>'127.0.0.1'],
        'deny'   => '*'
    ]
];