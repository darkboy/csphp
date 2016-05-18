<?php
namespace App\controlers\home;
use \Csp\base\CspBaseControler;
use \Csphp;

class demo extends CspBaseControler{

    public function __construct(){
        parent::__construct();
    }

    public function actionIndex(){
        echo 'Hello '.__FUNCTION__;
    }

    //日志使用示例
    public function actionLog(){
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
    public function actionTpl(){
        Csphp::view()->jsData('varKey','value assign in Controler');
        //单个 赋值
        $this->assign('c_v1', 'c-v1');
        //一次性赋值多个
        $this->assign(['c_v2'=>'c-v2<hello>']);
        //不提供参数将自动根据 控制器 以及 action 到 当前模块的 模板目录下去找对应的模板
        $this->render();
    }

    /**
     * 布局示例
     */
    public function actionLayout(){
        $this->assign('c_v1', 'c-v1');
        $this->assign(['c_v2'=>'c-v2<hello>']);

        $this->renderBylayout('index','.tpl');
    }


}

