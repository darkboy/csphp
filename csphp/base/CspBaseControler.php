<?php
namespace Csp\base;
use \Csphp;

class CspBaseControler {

    public $jsonpCallbackName = null;

    public function __construct(){

    }

    /*
     *
     */
    public function render($tplRoute='', $vars=array(), $isReturn=false){
        Csphp::tpl()->render($tplRoute, $vars, $isReturn);
    }

    public function assign($k, $v=null){}

    /**
     *
     */
    public function xpipeStart(){}

    public function xpipe(){}

    /**
     * 控制器实例化的时候 被执行 主要用于 访问控制
     * 返回一个ACL 配置
     * @return array
     */
    public function filter(){
        return array();
        /*
        return array(
            'acl'=>array(
                'filter'=>array(),
                'order' =>'deny,allow',
                'deny'  =>array(),
                'allow' =>array(),
            )
        );
        */
    }


    /**
     * 控制器前置HOOK，action 动作执行前 执行
     */
    public function beforeAction(){
    }
    /**
     * 控制器后置HOOK，action 动作执行后 执行
     */
    public function afterAction(){
    }


    /**
     * 接口API的标准输出
     * @param mixed     $rst
     * @param int       $code
     * @param string    $msg
     * @param string    $tips
     * @return string   JSON str
     */
    public function apiRst($rst, $code=0, $msg='OK', $tips=''){
        return Csphp::wrapJsonApiData($rst, $code, $msg, $tips);
    }

    public function ajaxRst($rst, $code=0, $msg='OK', $tips=''){
        return Csphp::wrapJsonApiData($rst, $code, $msg, $tips);
    }

    public function jsonpRst($rst, $code=0, $msg='OK', $tips=''){
        return $this->$jsonpCallbackName.'('.Csphp::wrapJsonApiData($rst, $code, $msg, $tips).');';
    }


}
