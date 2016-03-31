<?php
namespace Csp\core;
use \Csphp;
/*
 * Csphp 中关于url 的概念约定
 *
 * http://www.csphp.com:80/path/to/setup/index.php/controler/action/v1-v1/v2-v2?a=1&b=2#abc
 * 主机部分 http://www.csphp.com:80
 * 安装路径 /path/to/setup
 * 入口文件 /path/to/setup/index.php
 * 请求路由 /controler/action
 * 变量路由 /v1-v1/v2-v2
 * 查询串  ?a=1&b=2
 * 锚点名  #abc
 *
 *
 */
class CspRouter{

    /**
     * @var array 路由信息说明
     */
    public $routeInfo = array(
        //原始的完整 uri 信息
        'uri'           =>'',
        //项目的安装目录，入口文件之前的部分
        'setup_path'    =>null,
        'entry_file'    =>null,
        //URI 中 清除 变量路由 和 安装目录 后的路由
        'req_route'     =>null,
        //命中中的 路由规则名
        'hit_rule'      =>'',
        //解释后最终要执行的路由
        'real_rule'     =>'',  //最终要执行的 路由
        //从路由规则中解释出来的 变量字典，可能是来自URL中的 /v1-v1/v2-v2 或者是 路由配置中的 "user/{actionVar}"
        'route_var'     => array()
    );

    public $cliInfo = array();

    public function __construct(){

    }

    public function init(){
        $this->routeInfo['uri']        = Csphp::request()->getReqUri();
        $this->routeInfo['setup_path'] = $this->getSetupPath();
        $this->routeInfo['entry_file'] = $this->getEntryFile();
    }
    public function dump(){
        echo '<pre>';
        print_r($this->routeInfo);
    }
    /**
     * 某些场景下，项目会被安装在WEB站点的某个目录
     * 返回入口文件之前的路路径，不含最后的 / ，含左 /
     */
    public function getSetupPath(){
        if(Csphp::request()->isCli()){
            return '';
        }else{
            return substr(dirname($_SERVER['SCRIPT_FILENAME']), strlen($_SERVER['DOCUMENT_ROOT']));
        }
    }

    /**
     * 获取入口文件 包含 安装目录,左 /
     */
    public function getEntryFile(){
        return substr($_SERVER['SCRIPT_FILENAME'], strlen($_SERVER['DOCUMENT_ROOT']));
    }

    /**
     * 用户请求的路由，清除 路由变量,安装目录以及入口文件
     *
     */
    public function getReqRoute(){
        return $this->routeInfo['req_route'];
    }

    public function getUri(){
        return $this->routeInfo['uri'];
    }

    /**
     * 获取路由解释过程中产生的变量
     */
    public function getRouteVars(){
        return $this->routeInfo['route_var'];
    }

    /**
     * 设置一个路由变量
     * @param $k
     * @param null $v
     */
    public function setRouteVar($k, $v=null){
        if(is_array($k)){
            foreach($k as $kk=>$vv){
                $this->routeInfo['route_var'][$kk]=$vv;
            }
        }else{
            $this->routeInfo['route_var'][$k]=$v;
        }
    }


    /**
     * 解释当前请求 路由
     */
    public function parseRoute(){
        $this->parseUrl();
        $this->findRoute();
    }

    /**
     * 解释路径
     */
    public function parseUrl(){
        $urlPs      = explode('?', $this->getUri(),2);
        $urlPath    = trim($urlPs[0], '/\\');
        $urlPs      = explode('/', $urlPath);

        $reqRoute   = '';
        $startRouterVar = false;
        foreach($urlPs as $p){
            if($startRouterVar || strpos($p, '-')){
                $vs = explode('-',$p);
                $this->setRouteVar($vs[0], isset($vs[1]) ? urldecode($vs[1]) : '');
                $startRouterVar = true;
            }else{
                $reqRoute.='/'.$p;
            }
        }
        $this->routeInfo['req_route'] = $reqRoute;

    }


    /**
     * 从 路由配置 中查找 条件匹配的 规则
     */
    public function findRoute(){
        $reqRoute = $this->getReqRoute();
        foreach(Csphp::appCfg('router',array()) as $routeName=>$rCfg){
            if( !isset($rCfg['filter']) || Csphp::request()->isMatch($rCfg['filter']) ){
                //对请求路由进行预处理，如删除 静态化 的后缀 .html 等
                $reqRoute = $this->doRouteBeforeAction($reqRoute,$rCfg);
                $this->findMatchRuleByRouteCfg($reqRoute, $rCfg);
            }
        }
    }

    /**
     * 对 req route 进行预处理，通常是删除后缀
     * @param string    $reqRoute
     * @param array     $rCfg
     * @return string   $reqRoute
     */
    public function doRouteBeforeAction($reqRoute,$rCfg){
        if(!isset($rCfg['before_action']) || empty($rCfg['before_action'])){
            return $reqRoute;
        }
        return $this->doActionForReqRoute($reqRoute, $rCfg['before_action']);
    }

    /**
     * 对 req route 进行后处理，通常是添加 前缀
     * @param string    $reqRoute
     * @param array     $rCfg
     * @return string   $reqRoute
     */
    public function doRouteAfterAction($reqRoute,$rCfg){
        if(!isset($rCfg['after_action']) || empty($rCfg['after_action'])){
            return $reqRoute;
        }
        return $this->doActionForReqRoute($reqRoute, $rCfg['after_action']);

    }

    /**
     * 对请求路由做一些字符串操作，
     * @param $reqRoute  string 请求路由
     * @param $actionCfg array or string 如 array('del_suffix','.html') "del_suffix=>.html"
     * @return stirng
     * @throws \Csp\core\CspException
     */
    public function doActionForReqRoute($reqRoute, $actionCfg){
        if(!is_array($actionCfg)){
            $actionCfg = explode("::", $actionCfg, 2);
        }
        $actionName = $actionCfg[0];
        $actionArg = isset($actionCfg[1]) ? trim($actionCfg[1]) : '';
        switch(strtolower($actionName)){
            case 'del_suffix':
                if(substr($reqRoute,-strlen($actionArg))===$actionArg){
                    return substr($reqRoute, 0, -strlen($actionArg));
                }else{
                    return $reqRoute;
                }
                break;
            case 'add_suffix':
                return $reqRoute.$actionArg;
            case 'del_prefix':
                if(substr($reqRoute,0,strlen($actionArg))===$actionArg){
                    return substr($reqRoute, strlen($actionArg));
                }else{
                    return $reqRoute;
                }
                break;
            case 'add_prefix':
                return $actionArg.$reqRoute;
            case 'regexp_replace':
                $actionCfg2 = isset($actionCfg[2]) ? $actionCfg[2] : '';
                return preg_replace($actionArg,$actionCfg2, $reqRoute);
            default:
                throw new CspException('error route action confg: '.json_encode($actionCfg));
        }
    }

    /**
     * 从路由配置规则 rule_list 中查找 匹配的规则条件
     * 查找逻辑为
     * 1. 检查是否有别名，有则，立即返回
     * 2. 检查当前的 请求 路由是否 存在 控制器文件，存在则返回
     * 3. 逐一 将 配置 规则 翻译 为正则表达式，并进行匹配，成功立即返回
     *
     * 可能的配置规则为:
     * 1. 别名 ，如 /req_route/ctrl1/action=>/req_route/ctrl2/action2
     * 2. 配置规则中 可能 包含变量 ，所有变量都可以在 目标中使用
     *      总规则为 {var_name/default_value/type/len}
     *      var_name        表示变量名，可以在目标路由中引用 如 "/api/user/{var_name}"
     *
     *      default_value   表示默认值，默认值为空
     *
     *      type            表示匹配的字符类型,只有3种类型, 默认值为 s
     *              *   为任意字符
     *              d   数字
     *              s   为除 / 外的字符
     *
     *      len             表示匹配的字符的个数,默认值为 +
     *              *   为任意个字符,可以没有
     *              +   1个以上，不能没有
     *              1,3 1-3个
     *
     *
     *      {name}          表示任意长度的字符，不包括 / 符
     *      {name/guest/s}  表示 匹配一个 name 的变量，没有 以 guest 顶上
     *
     *
     *
     * @param $reqRoute string  请求路由
     * @param $rCfg     array   配置列表 可能的配置规则如下:
     *
     */
    public function findMatchRuleByRouteCfg($reqRoute, $rCfg){

    }




    /**
     * 用户自定义路由
     * @param $filter
     * @param $callback
     */
    public static function on($filter, $callback){

    }
}
