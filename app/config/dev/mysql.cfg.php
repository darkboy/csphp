<?php
/**
 * 数据库配置说明,应用可以使用多个数据库
 *
 * slave 的配置将会 与 主配置进行合并后作为从机的配置
 *
 * 配置示例 dsnName=>$dbConfig :
 *
 *    'default' => array(
 *       'is_default' => true,
 *       'master'     => array(
 *           'host'      => 'utf8',
 *           'port'      => '3306',
 *           'charset'   => 'utf8',
 *           'username'  => 'root',
 *           'password'  => '123456',
 *           'db_name'   => 'test',
 *           'tb_prefix' => 'csp_'
 *       ),
 *       'slaves'     => array(
 *           array('host' => 'slave1.db.host'),
 *           array('host' => 'slave2.db.host'),
 *       )
 *   ),
 *
 */
return array(
/*
    'default' => array(
        'is_default' => true,
        'master'     => array(
            'host'      => 'utf8',
            'port'      => '3306',
            'charset'   => 'utf8',
            'username'  => 'root',
            'password'  => '123456',
            'db_name'   => 'test',
            'tb_prefix' => 'csp_'
        ),
        'slaves'     => array(
            array('host' => 'slave1.db.host'),
            array('host' => 'slave2.db.host'),
        )
    ),
*/


    'dsn_2' => array(
        'is_default' => true,
        'master'     => array(
            'host'      => 'utf8',
            'port'      => '3306',
            'charset'   => 'utf8',
            'username'  => 'root',
            'password'  => '123456',
            'db_name'   => 'test',
            'tb_prefix' => 'csp_'
        ),
        'slaves'     => array(
            array('host' => 'slave1.db.host'),
            array('host' => 'slave2.db.host'),
        )
    ),

);

