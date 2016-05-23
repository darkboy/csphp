<?php
namespace App\controlers\home;
use \Csp\base\CspBaseControler;
use \Csp\core\CspRequest;
use \Csphp;

class demo extends CspBaseControler{

    public function __construct(){
        parent::__construct();

        //-----------------------------------------------------
        //运行时 中间件示例
        $runtimeMiddleware = [
            function(CspRequest $request, $next){
                echo '<pre>middleware runtime 1-start',"\n";
                $r = $next($request);
                echo 'middleware runtime 1-end',"\n";
                return $r;
            },
            function(CspRequest $request, $next){
                echo 'middleware runtime 2-start',"\n";
                $r = $next($request);
                echo 'middleware runtime 2-end',"\n";
                return $r;
            }
        ];
        //注册一个只在 middleware action 中执行的 中间件
        $this->useMiddleware($runtimeMiddleware, 'middleware');
        //-----------------------------------------------------
    }

    public function actionIndex(CspRequest $request){
        echo '<h1 style="text-align: center;margin-top: 100px;color: darkblue;">Hello Csphp demo... </h1>';
    }

    //日志使用示例
    public function actionLog(CspRequest $request){
        echo 'Hello log '.__FUNCTION__;

        /*日志测试，4种常日志 */
        Csphp::logDebug("logDebug...");
        Csphp::logInfo("logInfo {{test}}",['test'=>'demoLogVar']);
        Csphp::logWarning("logWarning {{test}}",['test'=>'demoLogVar']);
        Csphp::logError(['err'=>'errmsg'],['test'=>'demoLogVar']);
        Csphp::logError(['err'=>'errmsg'],['test'=>'demoLogVar'],'sql');

    }


    /**
     * 模板使用示例
     */
    public function actionTpl(CspRequest $request){
        Csphp::view()->jsData('varKey','value assign in Controler');
        //单个 赋值
        $this->assign('c_v1', 'c-v1');
        //一次性赋值多个
        $this->assign(['c_v2'=>'c-v2<hello>']);
        //不提供参数将自动根据 控制器 以及 action 到 当前模块的 模板目录下去找对应的模板
        $this->render();
    }

    /**
     * 布局模板使用 示例
     */
    public function actionLayout(CspRequest $request){
        $this->assign('c_v1', 'c-v1');
        $this->assign(['c_v2'=>'c-v2<hello>']);

        $this->renderBylayout('index','.tpl');
    }

    //helpers 中 以 .preload.php 结尾 的文件将会被自动预加载
    public function actionPreload(CspRequest $request){
        echo '<pre>preload_function_demo2 is not defined ',"\n";
        echo "\n",'preload_function_demo1 exists : '.(function_exists('\preload_function_demo1') ? 'true' : 'false');
        echo "\n",'preload_function_demo2 exists : '.(function_exists('\preload_function_demo2') ? 'true' : 'false');
        echo "\n",'preload_function_demo3 exists : '.(function_exists('\preload_function_demo3') ? 'true' : 'false');
    }


    //用户输入 与 输入验证 示例
    public function actionInput(CspRequest $request){
        $input = [
            'num'   => $request->get('num', 1, 'require,num'),
            'email' => $request->get('email', 1, 'require,email'),
            'ip'    => $request->get('ip', 1, 'norequire,ip'),
            'phone' => $request->get('phone', 1, 'norequire,phone'),
            'pcard' => $request->get('pcard', 1, 'norequire,pcard'),
        ];
        Csphp::dump($input);
    }

    public function actionJsonp(CspRequest $request){
        $this->useJsonp();
        echo $this->jsonpRst(true);
    }

    //路由配置示例用的控制器
    public function actionForRouteDemo(CspRequest $request){
        $actionName = $this->getActionName();
        echo '<pre>路由解释示例,forRouteDemo 当前命中的路由['.$actionName.']解释信息如下:',"\n";print_r(Csphp::router()->getRouteInfo());
    }

    public function actionFunc(){
        echo "Hello";

    }

    public function noActionPrefix(){
        $actionName = $this->getActionName();
        echo '<pre>路由解释示例,forRouteDemo 当前命中的路由['.$actionName.']解释信息如下:',"\n";print_r(Csphp::router()->getRouteInfo());
    }

    public function actionComp(){
        Csphp::comp('comp_demo')->hello();
    }

    //中间件测试
    public function actionMiddleware(CspRequest $request){
        echo "\n",'------- run action -------- ',"\n\n";
    }

    //----------------------------------------------------------
    public function actionAclDeny1(){
        $this->deny([
            'ip'=>'127.*.*.*'
        ]);

        echo 'hello ...';
    }
    public function actionAclDeny2(){
        $this->allow([
            '!ip'=>'127.*.*.*'
        ]);

        echo 'hello ...';
    }
    //全局ACL
    public function actionAclDeny3(){
        echo 'hello ...';
    }
    public function actionAclAllow(){
        $this->allow([
            'ip'=>'127.*.*.*'
        ]);

        echo 'hello ...';
    }

    //----------------------------------------------------------



}

