<?php
namespace Csp\core;
use \Csphp as Csphp;
use Csp\core\CspRouter as CspRouter;
class CspRequest{
    //请求类型
    const REQ_TYPE_WEB  = 'web';
    const REQ_TYPE_AJAX = 'ajax';
    const REQ_TYPE_JSONP= 'jsonp';
    const REQ_TYPE_API  = 'api';
    const REQ_TYPE_CLI  = 'cli';
    // request type is one of up const
    public static  $reqType = null;
    //用户自定义的用于查找请求的方法 name=>func($req)
    public static $reqFounderFunc = array();

    /**
     * @var \Csp\core\CspRouter
     */
    public $router = null;

    public function __construct(){
        $this->router = new CspRouter();
        $this->init();
    }

    /**
     * @return \Csp\core\CspRouter
     */
    public function router(){
        return $this->router;
    }

    /**
     * init request
     */
    public function init(){
        $this->initRouterInfo();
        $this->getRequestType();

    }

    /**
     * 简单初始化路由信息，
     */
    public function initRouterInfo(){

    }

    /**
     * @return null|string
     */
    public function getRequestType(){
        if(self::$reqType!==null){
            return self::$reqType;
        }
        if(strtolower(PHP_SAPI)==='cli'){
            self::$reqType = self::REQ_TYPE_CLI;
            return self::$reqType;
        }
        $filterCfgKeys = array(
            'is_jsonp_req'  => self::REQ_TYPE_JSONP,
            'is_api_rep'    => self::REQ_TYPE_API,
            'is_ajax_req'   => self::REQ_TYPE_AJAX
        );
        foreach($filterCfgKeys as $fk=>$type){
            $filter = Csphp::sysCfg($fk,null);
            if(is_array($filter)){
                if($this->isMatch($filter)){
                    self::$reqType = $type;
                    return $type;
                }
            }else{
                throw new CspException("Error filter config for $fk  : ".$filter);
            }
        }
        self::$reqType = self::REQ_TYPE_WEB;
        return self::$reqType;


    }

    public function getHttpMethod(){
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * 检查当前请求是否符合给定的条件
     * 以下所有 以 __rcf__ 为前缀的方法 都用于 过滤器 检查
     *
     * 注: 被检查路由什不包括 前后 /
     *
     * @param $requestFilter 请求 过滤器 描述字典 配置规则如下
     *
     *  filterName  =>argCfg        表示正过滤器
     *  !filterName =>filterCfg     在过滤器名前加 ! 号，将以上面逻辑相反，，反过滤器
     *
     *  同一过滤器有多个时，可以不使用 key , 系统对 数字 key 的配置，将按如下结构解释
     *
     *  array(filterName, filterCfg)
     *
        $requestFilter = array(
            'domain'        =>'*',   //对当前域名进行匹配,如果参数是数组，则匹配一个即可,如 "*.domain.com,www.domain.com"
            'ip'            =>'*',   //IP表达式如： "133.14.11.[1-28],133.22.22.222,111.234.222.*,133.14.11.33/34/38"

            'httpMethod'    =>'*',   //可以是数组或者逗号隔开的 HTTP方法 列表如 “GET,POST”
            'requestType'   =>'*',   //可以是数组或者逗号隔开的 请求类型 列表如： “api,web,cli,ajax,jsonp”
            'entryName'     =>'*',   //可以是数组或者逗号隔开的 入口名 列表如 home,api,admin

            'urlPrefix'     =>'*',   //可以是数组或者逗号隔开的 前缀列表,如 “index/*,user/*”
            'urlSuffix'     =>'*',   //可以是数组或者逗号隔开的 后缀列表,如 ".html"

            'match'         =>'*',   //可以是数组或者逗号隔开的 路由列表 glob 模式检查,如 "user/*"
            'regexp'        =>'*',   //正则表达式

            //检查用户输入,有一个符合条件即通过
            'inputOne'      =>array($vr,$value, $op),
            //检查用户输入,所有都须符合条件才通过
            'inputAll'      =>array($vr,$value, $op),

            //用户自定义查找规则,是一个可调用的回调函数或者已注册的 founderFunc
            //可以使用 CspRequest::registerFounder($founderName, $founderFunc) 注册自己的过滤器
            //$founderFunc 定义为 function(CspRequest){}; 参数为请求对象
            'userFounder'   =>'abc::abc',
        );
     */
    public function isMatch($requestFilter){
        foreach($requestFilter as $filterName=>$filterArg){
            //skip empty config
            if(is_numeric($filterName) && empty($filterArg)){continue;}

            if(is_numeric($filterName)){
                $filterName = $filterArg[0];
                $filterArg  = @$filterArg[1];
            }
            //如果过滤器名，是以 ! 开头 表示这是一个反过滤器，进行 反操作验证
            $firstChar = substr($filterName, 0, 1);
            $isNot = false;
            if($firstChar==='!'){
                $filterName = substr($filterName, 0, 1);
                $isNot = true;
            }
            $filterMethod = '__rcf__'.$filterName;
            if(!method_exists($this, $filterMethod)){
                throw new CspException("Error filter config for $filterName : ".json_encode($filterArg));
            }

            if( ($isNot===false && !$this->$filterMethod($filterArg))
                ||
                ($isNot===true  && $this->$filterMethod($filterArg))
                ){
                return false;
            }
        }
        return true;
    }

    /**
     * 检查当前的请求域名
     *
     * @param $filterArg 配置项可以是 逗号隔开的 域名列表，或者数组，可以使用通配符如  *.abc.com
     */
    private function __rcf__domain($filterArg){
        $host = $this->getHost();
        if(!is_array($filterArg)){
            $filterArg = explode(',', $filterArg);
        }
        foreach($filterArg as $pr){
            if(fnmatch($pr, $host)){
                return true;
            }
        }
        return false;
    }

    /**
     * IP 过滤器，符合条件时返回 true
     * @param $filterArg 为IP 列表表达式如： "133.14.11.[1-28],133.22.22.222,111.234.222.*,133.14.11.33/34/38";
     */
    private function __rcf__ip($filterArg){
        return $this->isInIpList($filterArg, $this->clientIp());
    }

    /**
     * requestType 过滤器
     *
     * @param $filterArg 配置值可以是 逗号隔开的 requestType，或者数组 如 “api,jsonp”
     * @return bool
     */
    private function __rcf__requestType($filterArg){
        if(!is_array($filterArg)){
            $filterArg = explode(",", strtoupper($filterArg));
        }
        return in_array($this->getRequestType(), $filterArg);
    }

    /**
     * 入口过滤器
     *
     * 检查请求是否从特定的入口进入，必须先在入口文件中配置 CSPHP_ENTRYNAME 常量
     *
     * @param $filterArg 配置值可以是 逗号隔开的 入口名，或者数组 如 “home,admin”
     * @return bool
     * @throws \Csp\core\CspException
     */
    private function __rcf__entryName($filterArg){
        if(defined('CSPHP_ENTRYNAME')){
            if(!is_array($filterArg)){
                $filterArg = explode(',', $filterArg);
            }
            return in_array(CSPHP_ENTRYNAME, $filterArg);
        }else{
            throw new CspException("Use  entryName filter , pls define CSPHP_ENTRYNAME const ");
        }
        return false;
    }
    /**
     * httpMethod 过滤器
     * 配置值可以是 逗号隔开的 HTTP方法值，或者数组 如 “GET，POST” or array("GET","DELETE")
     *
     * @param $filterArg
     * @return bool
     */
    private function __rcf__httpMethod($filterArg){
        if(!is_array($filterArg)){
            $filterArg = explode(",", strtoupper($filterArg));
        }
        return in_array($this->getHttpMethod(), $filterArg);
    }

    /**
     * 前缀过滤器，
     * @param $filterArg 可以是一个 逗号隔开的前缀列表，或者 数组
     * @return bool
     */
    private function __rcf__urlPrefix($filterArg){
        $reqRoute = $this->reqRoute();
        if(!is_array($filterArg)){
            $filterArg = explode(',', $filterArg);
        }
        foreach($filterArg as $prefix){
            $prefix = trim($prefix, ' /');
            if(substr($reqRoute,strlen($prefix))===$prefix){
                return true;
            }
        }
        return false;
    }

    /**
     * 后缀过滤器
     * @param $filterArg 可以是一个 逗号隔开的后缀列表，或者 数组
     * @return bool
     */
    private function __rcf__urlSuffix($filterArg){
        $reqRoute = $this->reqRoute();
        if(!is_array($filterArg)){
            $filterArg = explode(',', $filterArg);
        }
        foreach($filterArg as $suff){
            $suff = trim($suff, ' /');
            if(substr($reqRoute,-strlen($suff))===$suff){
                return true;
            }
        }
        return false;
    }

    /**
     * glob 模式过滤器
     * @param $fileArg  可以是用逗号隔开的 路由列表，或者数组，
     *      如 “user/*,post/create” 或者 array("user/*", "post/create")
     */
    private function __rcf__match($fileArg){
        return $this->isRouteInlist($this->reqRoute(), $fileArg);
    }

    /**
     * 正则过滤器
     * match 正刚过滤器的配置项为 , 被检查的是当前请求路由 不包含前后 /
     * @param $fileArg 正则表达式,或者正则表达式数组，符合一条即可
     */
    private function __rcf__regexp($fileArg){
        if(!is_array($fileArg)){
            $fileArg = array($fileArg);
        }
        $route = $this->reqRoute();
        foreach($fileArg as $regexp){
            $r = preg_match($regexp, $route);
            if($r===false){
                throw new CspException("Error filter config for regexp : ".$fileArg);
            }
            if($r>0) {return true;}
        }

        return false;
    }

    /**
     * 输入验证过滤器，有一个输入符合要求即可
     *
     * @param $filterArg 配置规则如下
     * array(
            array(vRoute) 表示有即可
            array(vRoute,$value,$op='==') 表示必须相等
            array(vRoute,$regexp,$op='regexp') 表示要符合某个正则
     * );
     * op操作 的可选值为 == != >= <= > < regexp any
     */
    private function __rcf__inputOne($filterArg){
        foreach($filterArg as $rule){
            if(empty($rule)) {
                continue;
            }
            if(call_user_func_array(array($this, '__rcf_chkValue'), $rule)){
                return true;
            }
        }
        return false;
    }

    /**
     * 输入验证过滤器，需要所有输入都符合要求
     * @param $filterArg 配置规则见 self::__rcf_chkValue
     */
    private function __rcf__inputAll($filterArg){
        foreach($filterArg as $rule){
            if(!call_user_func_array(array($this, '__rcf_chkValue'), $rule)){
                return false;
            }
        }
        return true;
    }

    /**
     * 检查一个变量是否符合某个规则
     * @param $vr               变量路由
     * @param null $valOrRule   变量值 或者 规则
     * @param string $op        操作符可能是 == != >= <= > < regexp any ci
     *
     */
    private function __rcf_chkValue($vr, $valOrRule=null, $op='any'){
        $v = Csphp::V($vr);
        //如果只配置了两个项目默认为判断是否相等
        if(func_num_args()==2){
            $op = '==';
        }
        switch(strtoupper($op)){
            case 'ANY' :
                return $v!==null;
            case '>' :
                return $v!==null && $v > $valOrRule;
            case '>=' :
                return $v!==null && $v >= $valOrRule;
            case '<' :
                return $v!==null && $v < $valOrRule;
            case '<=' :
                return $v!==null && $v <= $valOrRule;
            case '==' :
            case '=' :
                return $v!==null && $v == $valOrRule;
            case '!=' :
                return $v!==null && $v != $valOrRule;
            case 'CI' :
                return $v!==null && strtoupper($v) == strtoupper($valOrRule);
            case 'REGEXP':
                $r = preg_match($valOrRule, $v);
                if($r===false){
                    throw new CspException("Error filter config for input regexp : ".json_encode(array($vr, $valOrRule, $op)));
                }
                return $r>0;
                break;
            default:
                throw new CspException("Error filter config for input checker : ".json_encode(array($vr, $valOrRule, $op)));
                break;
        }
        return false;
    }
    /**
     * 用户自定义的过滤器
     *
     * @param $filterArg 闭包或者 callable callback 如： function (CspRequest){}
     */
    private function __rcf__userFounder($filterArg){

        if(is_string($filterArg) && isset(self::$reqFounderFunc[$filterArg])){
            return call_user_func(self::$reqFounderFunc[$filterArg], $this);
        }else{
            if(is_callable($filterArg)){
                return call_user_func($filterArg, $this);
            }else{
                throw new CspException("void userFounder ".json_encode($filterArg)." is not callable ");
            }
        }

        return false;
    }
    /**
     * 注册请求检查器，用于查找符合条件的 请求，可配置在 reqCondFilter 的 userFounder 字段
     * @param $founderName
     * @param $founderFunc  查找逻辑的 闭包 或者  callable ，
     */
    public function registerFounder($founderName, $founderFunc){
        self::$reqFounderFunc[$founderName] = $founderFunc;
    }

    /**
     * 检查是否在IP列表
     * @param $ipList = "133.14.11.[1-28],133.22.22.222,111.234.222.*,133.14.11.33/34/38";
     * @param null $ip  被检查的 IP 或者自动从系统取
     * @return bool
     */
    public function isInIpList($ipList, $ip=null){
        $ip = $ip===null ?  $this->clientIp() : $ip;
        if(!is_array($ipList)){
            $ipList = str_replace("|", ',', $ipList);
            $ipList = explode(',', $ipList);
        }
        $ipArr  = explode('.', $ip);

        foreach ($ipList as $ckIp) {
            $ckIpArr = explode('.', $ckIp);
            $chk = true;
            for($i=0;$i<4;$i++){
                if($ckIpArr[$i]=='*') {continue;}

                if(is_numeric($ckIpArr[$i])){
                    if($ckIpArr[$i]!=$ipArr[$i]){
                        $chk = false;
                        break;
                    }
                }else{
                    if(strpos($ckIpArr[$i], '/')){
                        if(!in_array($ipArr[$i],explode('/', $ckIpArr[$i]))){
                            $chk = false;
                            break;
                        }
                    }else{
                        $chkRange = explode('-', trim($ckIpArr[$i], "[] "));
                        if(!is_numeric($chkRange[0]) || $ipArr[$i]<$chkRange[0]){
                            $chk = false;
                            break;
                        }

                        if(!is_numeric($chkRange[1]) || $ipArr[$i]>$chkRange[1]){
                            $chk = false;
                            break;
                        }
                    }


                }
            }
            if($chk){
                return true;
            }
        }
        return false;

    }

    /**
     *
     * @param $routeName
     * @param $list     逗号隔开的 路由列表，或者数组，每一项将使用 fnmatch 检查
     * @return bool
     */
    public function isRouteInlist($routeName, $list){

        $rlist = is_array($list) ? $list : explode(',', $list);
        if (empty($routeName) || empty($list) || empty($rlist)){return FALSE;}
        foreach($rlist as $r){
            $r = trim($r);
            if (fnmatch($r, $routeName)){return TRUE;}
        }
        return FALSE;
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

    public function getHost(){
        return $_SERVER['HTTP_HOST'];
    }
    public function clientIp(){

    }

    //
    public function reqRoute(){
        return '';
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
$cfg['jsonp_flag_vr']=$reqCond;
$cfg['ajax_flag_vr'] =$reqCond;
$cfg['api_flag_vr']  =$reqCond;

//检查当前请求是否符合条件
Csp::isMatch($reqCond);
Csp::request()->isMatch($reqCond);
$reqCond=array(
    'domain'=>'*',          //当前域名
    'router_prefix'=>'*',   //路由前缀
    'request_method'=>'*',  //HTTP 请求方法 GET POST PUT CLI
    'router_suffix'=>'*',   //路由后缀
    'entry_name'=>'*',      //入口名称
    'client_ip'=>'*',       //访问者IP
    'header_send'=>'headerkey,value',     //发送了某个头信息 value 可选
    'user_cond'=>'abc::abc',//用户自定义规则,是一个可调用的 回调或者服务定义器
);

 */