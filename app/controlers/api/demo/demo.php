<?php
namespace App\controlers\api\demo;
use \Csp\base\CspBaseControler;
use \Csphp;

class demo extends CspBaseControler{

    public function __construct(){
    }

    public function actionGet(){
        $a = Csphp::request()->get('a',1);

    }

}

