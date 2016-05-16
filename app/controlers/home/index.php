<?php
namespace App\controlers\home;
use \Csp\base\CspBaseControler;
use \Csphp;

class index extends CspBaseControler{

    public function __construct(){
        parent::__construct();
    }

    public function filter(){
        //echo "\nfilter run...\n";
    }

    public function beforeAction(){
        //echo "\nbeforeAction\n";
    }
    public function afterAction(){
        //echo "\nafterAction\n";
    }

    public function actionGet(){}

    /**
     * 示例 action
     */
    public function actionIndex(){
        $this->render();
    }

    public function actionHome(){
        $this->render('.index');
    }

    public function actionLayout(){
        Csphp::tpl()->layout('index','.index');
    }

    public function pageletIndex(){

        //$this->render();
    }

    public function actionDebug(){
        echo 'Hello world!';
    }


    //日志使用示例
    public function actionLog(){
        /*日志测试，4种常日志 */
        Csphp::logDebug("logDebug...");
        Csphp::logInfo("logInfo {{test}}",['test'=>'demoLogVar']);
        Csphp::logWarning("logWarning {{test}}",['test'=>'demoLogVar']);
        Csphp::logError(['err'=>'errmsg'],['test'=>'demoLogVar']);
        Csphp::logError(['err'=>'errmsg'],['test'=>'demoLogVar'],'sql');
    }

}

