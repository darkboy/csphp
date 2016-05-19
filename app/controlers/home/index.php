<?php
namespace App\controlers\home;
use \Csp\base\CspBaseControler;
use \Csp\core\CspRequest;
use \Csphp;

class index extends CspBaseControler{

    public function __construct(){
        parent::__construct();
    }
    /**
     * 示例 action
     */
    public function actionIndex(){
        $this->render();
    }

}

