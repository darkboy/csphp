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

    public function pageletIndex(){

        $this->render();
    }

}

