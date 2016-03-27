<?php
namespace Csp\core;
use \Csphp as Csphp;

class CspRequest{
    //请求类型
    const REQ_TYPE_WEB  = 'WEB';
    const REQ_TYPE_AJAX = 'AJAX';
    const REQ_TYPE_API  = 'API';
    const REQ_TYPE_CLI  = 'CLI';
    // request type is one of up const
    public static  $reqType = null;
    public function __construct(){
        $this->init();
    }

    /**
     * init request
     */
    public function init(){
        $this->getRequestType();

    }
    public function getRequestType(){
        if(self::$reqType!==null){
            return self::$reqType;
        }
        if(strtolower(PHP_SAPI)==='cli'){
            self::$reqType = self::REQ_TYPE_CLI;
            return self::$reqType;
        }

    }

    /**
     * 检查当前请求是否符合给定的条件
     *
     * @param $reqCond 请求条件描述字典 规则如下
        $reqCond=array(
            'domain'=>'*',          //对当前域名进行匹配
            'router_prefix'=>'*',   //路由前缀
            'request_method'=>'*',  //HTTP 请求方法 GET POST PUT CLI
            'router_suffix'=>'*',   //路由后缀
            'entry_name'=>'*',      //入口名称
            'header_send'=>'headerkey,value',   //发送了某个头信息 value 可选
            'user_cond'=>'abc::abc',            //用户自定义规则,是一个可调用的 回调或者服务定义器
        );
     */
    public function isMatch($reqCond){
        foreach($reqCond as $condName=>$condArg){

        }
    }


    /**
     * get a param
     * @param $vr
     * @param null $def
     * @param string $rule
     * @param string $tips
     * @param null $errHandle
     */
    public function param($vr, $def=null, $rule='', $tips='', $errHandle=null){

    }

    public function clientIp(){

    }

    public function reqRoute(){

    }

}
/*

//request input
Csp::request()->param($kr,$def,$rule,$tips='',$errHandle);
Csp::request()->apiParam($kr,$def,$rule,$tips='',$errHandle);
Csp::request()->cliParam($kr,$def,$rule,$tips='',$errHandle);
Csp::request()->webParam($kr,$def,$rule,$tips='',$errHandle);
Csp::request()->ajaxParam($kr,$def,$rule,$tips='',$errHandle);
Csp::request()->jsonpParam($kr,$def,$rule,$tips='',$errHandle);
//获取请求类型
Csp::request()->getRequestType();//return ajax jsonp api web cli

Csp::request()->header($k);
Csp::request()->post();
Csp::request()->get();
Csp::request()->cookie();
Csp::request()->file();

//与URL相关的信息
Csp::request()->getHost();
Csp::request()->uri();
Csp::request()->lastViewUrl();//用户最后一次浏览的 url

//请求性质判断
Csp::request()->isApi();
Csp::request()->isAjax();
Csp::request()->isJsonp();
Csp::request()->isPost();
Csp::request()->isGet();
Csp::request()->isPut();
Csp::request()->isRobot();
Csp::request()->isPhone();

//用于判断 请求类型的相当配置
$cfg['jsonp_flag_vr']=array('g:callback', 'p:callback');
$cfg['ajax_flag_vr'] =array('g:_', 'p:_' );
$cfg['api_flag_vr']  =array('h:csp-api');

//检查当前请求是否符合条件
Csp::isMatch($reqCond);
Csp::request()->isMatch($reqCond);
$reqCond=array(
    'domain'=>'*',          //当前域名
    'router_prefix'=>'*',   //路由前缀
    'request_method'=>'*',  //HTTP 请求方法 GET POST PUT CLI
    'router_suffix'=>'*',   //路由后缀
    'entry_name'=>'*',      //入口名称
    'header_send'=>'headerkey,value',     //发送了某个头信息 value 可选
    'user_cond'=>'abc::abc',//用户自定义规则,是一个可调用的 回调或者服务定义器
);


 */