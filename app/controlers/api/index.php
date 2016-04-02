<?php
namespace App\controlers\api;
use \Csp\base\CspBaseControler;
use \Csphp;

class index extends CspBaseControler{
    public $var= 'test';

    public function __construct(){
        echo ' -init- ';
    }

    public function actionGet(){}

}

