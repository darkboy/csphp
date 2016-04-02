<?php
namespace App\controlers\home;
use \Csp\base\CspBaseControler;
use \Csphp;

class index extends CspBaseControler{
    public $var= 'test';

    public function __construct(){
        parent::__construct();
    }

    public function filter(){
        echo "\nfilter run...\n";
    }

    public function beforeAction(){
        echo "\nbeforeAction\n";
    }
    public function afterAction(){
        echo "\nafterAction\n";
    }

    public function actionGet(){}

    public function actionIndex(){
        echo 'Hello word index';
    }

}

