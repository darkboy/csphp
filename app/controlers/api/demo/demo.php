<?php
namespace App\controlers\api\demo;
use \Csp\base\CspBaseControler;
use \Csp\core\CspRequest;
use \Csphp;

class demo extends CspBaseControler{

    public function __construct(){
    }

    public function actionDemo1(){
        $a = Csphp::request()->get('id',1,'require,num');
    }

    public function actionDemo2(CspRequest $request){
        $a = $request->get('id',1,'require,num');
    }

    public function actionDemo3(){
        $a = $this->get('id',1,'require,num');
    }

}

