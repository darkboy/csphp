<?php
//slave 的配置将会 与 主配置进行合并后作为从机的配置
return array(
    'db_name'=>array(
        'master'=>array(
            'charset'   =>'utf8',
            'user'      =>'root',
            'pwd'       =>'123456',
            'port'      =>'3306',
            'db_name'   =>'test',
            'tb_prefix' =>'csp_',
            'init_sql'  =>'',

        ),
        'slaves'=>array(
            array('host'=>'slave1.db.host'),
            array('host'=>'slave2.db.host'),
        ),
    ),
);

