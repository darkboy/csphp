<?php
namespace Csp\comp\concurrent;
use \Csp\base\CspBaseControler;
use \Csp\base\CspBaseComponent;
use \Csphp;


/**
 *
 * Class CspCompConcurrentYar
 * @package Csp\comp
 */
class CspCompConcurrentYar extends CspBaseComponent {

    public function __construct() {
        parent::__construct();
    }

    public function start(){
        //Csphp::request();
    }



}





class yar extends CspBaseControler{

    public function __construct(){
        parent::__construct();
    }

    public function filter(){
        return array(
        );
    }

    //yar server 入口
    public function actionStart(){
        $service = new \Yar_Server(new yarserverProxyApi());
        $service->handle();
    }


}