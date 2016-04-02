<?php
namespace Csp\base;
use \Csphp;

class CspBaseControler {

    public $jsonpCallbackName = null;

    public function __construct(){
        $this->request = Csphp::request();
        $acls = $this->filter();
        Csphp::checkAccessControl($acls);
    }

    /**
     * 控制器实例化的时候 被执行 主要用于 当前于整个控制器的初始化 和 访问控制
     * @return array
     */
    public function filter(){
        return array(
            'acl'=>array(
                'filter'=>array(),
                'order' =>'deny,allow',
                'deny'  =>array(),
                'allow' =>array(),
            )
        );
    }


    /**
     * 控制器前置HOOK，在某个 action 动作执行前 执行
     */
    public function beforeAction(){

    }
    /**
     * 控制器后置HOOK，在某个 action 动作执行后 执行
     */
    public function afterAction(){

    }


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
