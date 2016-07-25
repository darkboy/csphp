<?php
namespace App\controlers\api\pcdd;
use \Csp\base\CspBaseControler;
use \Csphp;

class index extends CspBaseControler{
    public $var= 'test';

    public function __construct(){
        echo ' -init- ';
    }

    public function actionGet(){
        echo "hello.....";
    }

}

