<?php
namespace Csp\base;
use Csp\core\CspException;
use Csphp;

class CspBaseControler {

    //使用JSONP时的 JS调用方法名
    private $jsonpCallbackName  = 'cspCallback';

    public function __construct(){

    }
    //--------------------------------------------------------------------------------
    /**
     * 获取当前 请求的 Action 名称 是全名
     */
    public function getActionName(){
        return Csphp::router()->getActionName();
    }
    //--------------------------------------------------------------------------------
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
     * @param string|closure|array  $middleware  在控制器 特定的中间间
     */
    public function useMiddleware($middleware, $filters=[]){
        return Csphp::useMiddleware($middleware, $filters);
    }
    public function getCtrlMiddleware(){
        return $this->ctrlMiddlewares;
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

    //--------------------------------------------------------------------------------
    /**
     * 渲染一个模板
     * @param string    $tplRoute
     * @param array     $vars
     * @param bool      $isReturn
     */
    public function render($tplRoute='', $vars=array(), $isReturn=false){
        return Csphp::view()->render($tplRoute, $vars, $isReturn);
    }

    /**
     * @param        $layoutTpl
     * @param string $tplRoute
     * @param array  $vars
     * @param bool   $isReturn
     *
     * @return string
     */
    public function renderBylayout($layoutTpl, $tplRoute='', $vars=array(), $isReturn=false){
        return Csphp::view()->renderBylayout($layoutTpl, $tplRoute, $vars, $isReturn);
    }

    /**
     * 给模板赋值
     * @param string    $k
     * @param null      $v
     */
    public function assign($k, $v=null){
        Csphp::view()->assign($k, $v);
    }
    //--------------------------------------------------------------------------------

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

    /**
     * 如果当前 action 是 jsonp 请求
     * 则在action中调用此方法，以确定使用 jsonp
     * @param string $cbVarRoute
     */
    public function useJsonp($cbVarRoute='cspCallback'){
        if($cbVarRoute[1]!==':'){
            $cbVarRoute = 'G:'.$cbVarRoute;
        }
        $this->jsonpCallbackName = Csphp::request()->param($cbVarRoute,null,'require,slen:2-50,callback');
        return $this;
    }

    /**
     * 显示地设置 jsonp 的JS回调方法名称
     * @param $n
     *
     * @return $this
     */
    public function setJsonpCallbackName($n){
        $this->jsonpCallbackName = $n;
        return $this;
    }

    /**
     * jsonp 结果包装
     * @param        $rst
     * @param int    $code
     * @param string $msg
     * @param string $tips
     *
     * @return string
     */
    public function jsonpRst($rst, $code=0, $msg='OK', $tips=''){
        return $this->jsonpCallbackName.'('.Csphp::wrapJsonApiData($rst, $code, $msg, $tips).');';
    }


}
