<?php
namespace App\controlers\api\demo;
use \Csp\base\CspBaseControler;
use \Csp\core\CspRequest;
use \Csphp;

class demo extends CspBaseControler{

    public function __construct(){
    }

    public function actionDemo1(CspRequest $request){
        return [1,2,3];
    }

    public function actionDemo2(CspRequest $request){
        return $this->apiRst([1,2,3]);
    }

    public function actionDemo3(CspRequest $request){
        $this->apiRst([1,2,3]);
    }

}

