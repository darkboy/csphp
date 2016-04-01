<?php
namespace App;
//访问控制列表,
return array(
    array(
        //在什么条件下启用这条访问控制规则
        'filter'=>array(
            'module_name'=>'admin'
        ),
        //规则名称
        'acl_name'  =>'demo1',
        //检查顺序 deny 在前表示先检查 deny
        'order'     =>'deny,allow',
        //禁止访问的规则
        'deny'      =>array(),
        //允许访问的规则
        'allow'     =>array()
    )
);