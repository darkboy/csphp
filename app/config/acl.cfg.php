<?php
namespace App;
/**
 * 访问控制配置，deny 与 allow 的定义顺序会影响逻辑
 *  acl_name                    配置名称
 *      target,deny,allow
 *      target,allow,deny
 *
 *
 */
return [
    'acl_name' => [
        'target' => [
            'match'=>'/demo/aclDeny3'
        ],

        'deny'   => '*'
    ]
];