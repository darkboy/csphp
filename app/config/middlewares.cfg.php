<?php
namespace App\config;
/**
 * 中间件的配置说明:
 *
 *  使用 中间件 可以在 请求动作 被执行前 执行后 嵌入自定义逻辑， 常用于，访问控制，登录验证
 *
 *  中间件 包含 一个 固定方法请求处理方法，默认名为 handler
 *
 *  中间件的 请求处理方法 定义示例如下:
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
 *  配置提供的信息主要用于类的实例化, 有两种配置方式，
 *
 *  如果不需要自定义初始化选择 与 过滤器，可以直接配置为 类名字符串如:
 *      @libs/middleware/middlewareDemo:1,2
 *  如果需要自定义过滤器 或者初始化选项，则配置为数组
 *      [
 *          filter  =>   过滤器，在什么条件执行这个中间件
 *          function=>   中间件需要执行的目标逻辑 ,也可以直接写一个闭包,可能的形式如下:
 *                          @libs/middleware/demo
 *                          @libs/middleware/demo:1,2
 *                          function (CspRequest $request, Closure $next){}
 *          options =>   中间件的初始化选项，是可选的,如果配置了，刚会在实例化中间件时先执行: middlerWareObj->{$options[method]}($args);
 *      ]
 *
 */
return [
    //不需要自定义初始化选择 与 过滤器，可以直接配置为 类名字符串
    '@libs/middleware/middlewareDemo:1,2',
    //需要定自义 过滤器 或者 初始化选择，则配置为数组
    [
        //中间件的执行条件，
        'filter'    =>[],
        'function'  =>'@lib/middlerware/middlewareDemo',
        //中间件的初始化选项，是可选的
        //如果配置了，刚会在实例化中间件时先执行: middlerWareObj->{$options[method]}($args);
        'options'   =>[
            'method'=>'setInitOptions',
            'args'  =>[]
        ]
    ],

];

