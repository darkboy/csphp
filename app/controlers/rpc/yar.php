<?php
namespace App\controlers\rpc;
use \Csp\base\CspBaseControler;
use \Csphp;

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

/**
 * Class yarserverHelperApi
 * @package App\controlers\rpc
 */
class yarserverProxyApi{
    public $runKey = 0;
    public function __construct(){

    }

    /**
     * @param $k
     * @param $route
     * @param $method
     * @param $args
     * @param array $extParams
     */
    public function proxy($k, $route,  $method, $args, $extParams=array()){

    }

    /**
     * Yar api 测试函数,直接返回用户输入
     * @param mixed     $arg
     * @return mixed
     */
    public function ping($arg){
        return $arg;
    }


    /**
     * @param string   $msg     错误信息
     * @param int      $code    错误码
     * @return array            返回 yar server 标准结果集
     */
    private function error($msg,$code=300001){
        return $this->warpRst(false,$code, $msg);
    }
    /**
     * 包装能用的RPC结果
     * @param $rst
     * @param $code     错误码，默认为 0 ， >0 时 为错误码
     * @param $msg      错误信息，默认为无错误 OK
     * @return array    标识的 yar server 结果集
     */
    private function warpRst($rst, $code=0, $msg='OK'){
        return array(
            'key'=>$this->runKey,
            'msg'=>$msg,
            'code'=>$code,
            'time'=>(microtime(ture)-$this->timeStart),
            'rst'=>$rst
        );
    }
}

