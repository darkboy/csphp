<?php
namespace Csp\base;
use \Csphp;

class CspBaseControler {

    public $jsonpCallbackName = null;

    public function __construct(){

    }

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
     * 渲染一个模板
     * @param string    $tplRoute
     * @param array     $vars
     * @param bool      $isReturn
     */
    public function render($tplRoute='', $vars=array(), $isReturn=false){
        return Csphp::tpl()->render($tplRoute, $vars, $isReturn);
    }

    /**
     * 给模板赋值
     * @param string    $k
     * @param null      $v
     */
    public function assign($k, $v=null){
        Csphp::tpl()->assign($k, $v);
    }

    public function layout($layoutRoute, $tplRoute='', $vars=array(), $isReturn=false){
        return Csphp::tpl()->layout($layoutRoute, $tplRoute, $vars, $isReturn);
    }

    public function xpipe(){}
    public function async(){}


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
