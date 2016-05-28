<?php
namespace App\controlers\home;
use App\models\modelDemo;
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
        echo '<hr><div style="padding: 100px;"> ';
        $methods = get_class_methods(__CLASS__);
        foreach($methods as $fn){
            if(substr($fn,0,6)!=='action'){continue;}
            $action = lcfirst(substr($fn,6));
            echo '<span style="float: left;padding:2px;margin: 5px;"><a href="/demo/'.$action.'">'.$action.'</a></span>';
        }
        echo '</div> ';
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
            'num'   => $request->get('num', null,   'require,num'),
            'email' => $request->get('email', null, 'require,email'),
            'ip'    => $request->get('ip', '',    'norequire,ip'),
            'phone' => $request->get('phone', '', 'norequire,phone'),
            'pcard' => $request->get('pcard', '', 'norequire,pcard'),
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
    public function actionDb(){
        /**
         *@var $db \Csp\comp\db\CspcompDBMysqli
         *@var $db \App\models\modelDemo
         */
        $db = Csphp::comp('DB');
        echo '<pre>';print_r($db);


        $obj = modelDemo::getInstance();
        $obj["f1"] = '';
        $obj["f2"] = '';
        $obj["f3"] = '';
        $id = $obj->save();
        $id = $obj->delete();

        $data=[];
        $data["f1"] = '';
        $data["f2"] = '';
        $data["f3"] = '';
        $id = modelDemo::save($data=null);
        $id = modelDemo::create($data=null);
        $id = modelDemo::insert($data=null);



        $id = modelDemo::update($data=null, $condOrPk=null);
        $id = modelDemo::delete($condOrPk=null);


        $opt=null;

        $demo = modelDemo::withQueryOption($opt)->get(1);
        $demo = modelDemo::withCount($opt)->get(1);

        $demo = modelDemo::withCache($opt)->gets([1,2]);
        $demo = modelDemo::withFormat($opt)->get(1);
        $demo = modelDemo::withFields($opt)->get(1);

        $demo = modelDemo::withXhprof($opt)->get(1);
        $demo = modelDemo::withTime($opt)->get(1);
        $demo = modelDemo::withTrace($opt)->get(1);

        modelDemo::where()->withCache()->find();
        modelDemo::where()->withNoCache()->find();

    }
    //----------------------------------------------------------



}

