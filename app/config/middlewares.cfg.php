<?php
namespace App\config;
use Csphp;
use Csp\core\CspRequest;
/**
 * 中间件说明:
 *
 *  使用 中间件 可以在 请求动作 被执行前 执行后 嵌入自定义逻辑，
 *  常用于，访问控制，登录验证,等全局性的请求干预逻辑，$request 对象将被传递给每一个中间件进行处理，
 *
 *
 *  中间件类 包含 一个 固定方法请求处理方法，默认名为 handler
 *
 *  中间件的 请求处理方法 定义示例如下:
 *
 *  public function handler (CspRequest $request, Closure $next){
 *
 *      //写在这里的代码 将在 请求动作 被执行前 执行
 *
 *      $response = $next($request);
 *
 *      //写在这里的代码 将在 请求动作 被执行后 执行
 *
 *      return $response;
 *
 *  }
 *
 *
 *  配置说明: 配置提供的信息主要用于类的实例化, 有两种配置方式，
 *
 *  如果不需要自定义初始化选择 与 过滤器，可以直接配置为 类名字符串 或者 闭包如:
 *      使用默认处理方法 handler ,如下两种定义是一样的
 *      @libs/middleware/middlewareDemo:1,2
 *      @libs/middleware/middlewareDemo::handler:1,2
 *
 *      //使用自定义的处理方法 @libs/middleware/ 可以省略不写,如下两种定义是一样的
 *      @libs/middleware/middlewareDemo::customHandler:1,2
 *      middlewareDemo::customHandler:1,2
 *
 *      function (CspRequest $request, Closure $next){}
 *  如果需要自定义过滤器 或者初始化选项，则配置为数组
 *      [
 *          filter  =>   过滤器，在什么条件执行这个中间件
 *          target  =>   中间件需要执行的目标逻辑 ,也可以直接写一个闭包,可能的形式如下:
 *                          @libs/middleware/demo
 *                          @libs/middleware/demo:1,2
 *                          function (CspRequest $request, Closure $next){}
 *          options =>   中间件的初始化选项，是可选的,如果配置了，刚会在实例化中间件时先执行: middlerWareObj->{$options[method]}($args);
 *      ]
 *
 */
return [
    /*

    //配置示例1: 全局的类中间件
    '@lib/middlewares/middlewareDemo:1,1',
    '\App\libs\middlewares\middlewareDemo::customHandler',
    'middlewareDemo::customHandler:short1,short2',

    //配置示例2: 全局的 闭包中间件
    function(CspRequest $request, $next){
        echo "BeforeAction in closure middleware config\n";
        $response = $next($request);
        echo "AfterAction  in closure middleware config\n";
        return $response;
    },

    */

    //配置示例3: 需要定自义 过滤器 或者 初始化选项，则配置为数组
    [
        //中间件的执行条件，
        'filter'    =>['match'=>'/demo/middleware*'],
        //目标，可以省略前缀
        'target'    =>'middlewareDemo',
        //中间件的初始化选项，是可选的
        //如果配置了选项，刚会在实例化中间件时先执行: middlerWareObj->{$options[method]}($args);
        'options'   =>[
            'method'=>'setInitOptions',
            'args'  =>['v1'=>'v1-in-arr-cfg', 'v2'=>'v2-in-arr-cfg']
        ]
    ],

];

