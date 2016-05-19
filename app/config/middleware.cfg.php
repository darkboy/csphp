<?php
namespace App\config;
/**
 * 中间件的配置说明:
 *
 *  使用 中间件 可以在 请求动作 被执行前 执行后 嵌入自定义逻辑， 常用于，访问控制，登录验证
 *  中间件 包含 一个 固定方法请求处理方法，默认名为 handler
 *
 *  中间件的方法定义示例如下:
 *
 *  public function handler (CspRequest $request, Closure $next){
 *
 *      //与在这里的代码 将会在 请求动作 被执行前执行
 *
 *      $response = $next($request);
 *
 *      //写在这里的代码将在 请求动作 被执行完后执行
 *
 *      return $response;
 *
 *  }
 *
 *
 *  配置提供的信息主要用于类的实例化, 包括如下信息
 *
 *      filter  =>   过滤器，在什么条件执行这个中间件
 *      func    =>   中间件需要执行的目标逻辑 ,后缀 ::handler 是否选的，也可以直接写一个闭包
 *      options =>   中间件的初始化选项，是可选的,如果配置了，刚会在实例化中间件时先执行: middlerWareObj->{$options[method]}($args);
 *
 */
return [
    [
        //中间件的执行条件，
        'filter'    =>[],
        'function'  =>'@ext/dirname/classname::handler',
        //中间件的初始化选项，是可选的
        //如果配置了，刚会在实例化中间件时先执行: middlerWareObj->{$options[method]}($args);
        'options'   =>[
            'method'=>'setInitOptions',
            'args'  =>[]
        ]
    ],

];

