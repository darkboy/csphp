<?php
namespace Csp\core;
use \Csphp;
use Csp\core\CspRouter;
class CspRequest{
    //请求类型
    const REQ_TYPE_WEB  = 'web';
    const REQ_TYPE_AJAX = 'ajax';
    const REQ_TYPE_JSONP= 'jsonp';
    const REQ_TYPE_API  = 'api';
    const REQ_TYPE_CLI  = 'cli';

    // request type is one of web api ajax jsonp cli
    public static  $reqType = null;

    //用户自定义的用于查找请求的方法 name=>func($req)
    public static $reqFounderFunc = array();


    public function __construct(){
    }

    /**
     * @return \Csp\core\CspRouter
     */
    public function router(){
        return Csphp::router();
    }

    /**
     *
     * init request
     */
    public function init(){
        $this->getRequestType();
        $this->initParamErrorHandle();
    }



    /**
     * 初始化默认的参数错误处理函数
     */
    public function initParamErrorHandle(){
        //todo...
    }

    public function isApi(){
        return self::$reqType === self::REQ_TYPE_API;
    }
    public function isAjax(){
        return self::$reqType === self::REQ_TYPE_AJAX;
    }
    public function isJsonp(){
        return self::$reqType === self::REQ_TYPE_JSONP;
    }
    public function isWeb(){
        return self::$reqType === self::REQ_TYPE_WEB;
    }
    public function isCli(){
        return strtolower(PHP_SAPI)==='cli';
    }

    public function isPost(){
        return $this->getHttpMethod()==='POST';
    }
    public function isGet(){
        return $this->getHttpMethod()==='GET';
    }
    public function isPut(){
        return $this->getHttpMethod()==='PUT';
    }
    public function isDelete(){
        return $this->getHttpMethod()==='DELETE';
    }

    /**
     * 是否网络爬虫
     * @return bool
     */
    public function isRobot(){
        static $isRobot = NULL;
        if (NULL === $isRobot) {
            $isRobot  = FALSE;
            $robotlist = 'bot|spider|crawl|nutch|lycos|robozilla|slurp|search|seek|archive';
            if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/{$robotlist}/i", $_SERVER['HTTP_USER_AGENT'])) {
                $isRobot = TRUE;
            }
        }
        return $isRobot;
    }

    /**
     * 是否移动终端
     * @return bool
     */
    public function isPhone(){
        $devices = array("operaMobi" => "Opera Mobi", "android" => "android", "blackberry" => "blackberry", "iphone" => "(iphone|ipod)", "opera" => "opera mini", "palm" => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)", "windows" => "windows ce; (iemobile|ppc|smartphone)", "generic" => "(kindle|mobile|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap)");

        if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return TRUE;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/vnd.wap.wml') > 0 || strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') > 0)) {
            return TRUE;
        } else {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                foreach ($devices as $device => $regexp) {
                    if (preg_match("/" . $regexp . "/i", $_SERVER['HTTP_USER_AGENT'])) {
                        return TRUE;
                    }
                }
            }
        }

        return FALSE;
    }

    /**
     * 获取客户端UA
     * @return string
     */
    public function getUserAgent(){
        return $this->isCli() ? 'cli' : $_SERVER['HTTP_USER_AGENT'];
    }
    /**
     * 获取主机名
     * @return string
     */
    public function getHost(){
        return $this->isCli() ? 'cli' : $_SERVER['HTTP_HOST'];
    }

    public function getQueryString(){
        return $this->isCli() ? 'cli' : $_SERVER['QUERY_STRING'];
    }

    /**
     * 获取客户端IP
     * @return string
     */
    public function getClientIp(){
        static $ip=null;
        if($ip!==null){
            return $ip;
        }
        if($this->isCli()){
            return '0.0.0.0';
        }else{
            //用户可以指定 获取ip的尝试顺序，注，在使用 反向代理后，一般不能使用 REMOTE_ADDR ，不同的服务器，可能设置为不同的KEY
            $ipKeysOrder = Csphp::sysCfg('ip_keys_order', array('HTTP_X_FORWARDED_FOR','REMOTE_ADDR'));
            foreach($ipKeysOrder as $k){
                if (isset($_SERVER[$k]) && $_SERVER[$k]) {
                    $ipTmp = $_SERVER[$k];
                }
                $ipPattern = '/^((25[0-5]|2[0-4]\d|(1\d|[1-9])?\d)\.){3}(25[0-5]|2[0-4]\d|(1\d|[1-9])?\d)$/';
                //为保证安全，过滤掉不安排的IP
                $ip = preg_match($ipPattern, $ipTmp) ? $ipTmp : '0.0.0.0';

            }
            return $ip;
        }
    }
    //todo uri 清除路由变量,querystring,  左 / ,
    public function getReqRoute(){
        return '';
    }

    /**
     * @return string
     */
    public function getReqUri(){
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * 获取最后一次浏览的URL
     * @return string
     */
    public function getLastViewUrl(){
        if($this->isGet()){
            return $_SERVER['REQUEST_URI'];
        }else{
            return $_SERVER['HTTP_REFERER'];
        }
    }


    /**
     * 获取用户输入
     * @param string    $vr        变量路由
     * @param mixed     $def       默认值
     * @param string    $rule      验证规则列表
     * @param string    $tips      输入描述
     * @param callable  $errHandle callable function($voidInfo)
     * @return mixed
     */
    public function param($vr, $def=null, $rule='', $tips='', $errHandle=null){
        if($errHandle===null){
            $errHandle=null;
        }
        return Csphp::V($vr, $def, $rule, $tips, $errHandle);
    }
    public function get($kr, $def=null, $rule='', $tips='', $errHandle=null){
        return $this->param('G:'.$kr, $def, $rule, $tips, $errHandle);
    }
    public function post($kr, $def=null, $rule='', $tips='', $errHandle=null){
        return $this->param('P:'.$kr, $def, $rule, $tips, $errHandle);
    }
    public function cookie($kr, $def=null, $rule='', $tips='', $errHandle=null){
        return $this->param('C:'.$kr, $def, $rule, $tips, $errHandle);
    }
    public function file($kr, $def=null, $rule='', $tips='', $errHandle=null){
        return $this->param('F:'.$kr, $def, $rule, $tips, $errHandle);
    }
    public function header($kr, $def=null, $rule='', $tips='', $errHandle=null){
        return $this->param('H:'.$kr, $def, $rule, $tips, $errHandle);
    }

    /**
     * 获取POST原始输入
     * @return string
     */
    public function getRowInput(){
        return file_get_contents('php://input');
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
            'is_api_req'    => self::REQ_TYPE_API,
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

    /**
     * 获取HTTP method
     * @return string
     */
    public function getHttpMethod(){
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * 请求过滤器，检查当前请求是否符合给定的条件
     * 以下所有 以 __rcf__ 为前缀的方法 都用于 过滤器 检查
     *
     * 注: 被检查路由什不包括 前后 /
     *
     * @param $requestFilter array 请求 过滤器 描述字典 配置规则如下
     *
     *  filterName  =>filterCfg     表示正过滤器
     *  !filterName =>filterCfg     在过滤器名前加 ! 号，将以上面逻辑相反，反过滤器
     *
     *  同一过滤器有多个时，可以不使用 key , 系统对 数字 key 的配置，将按如下结构解释
     *
     *  array(filterName, filterCfg)
     *
        $requestFilter = array(
            'domain'        =>'*',   //对当前域名进行匹配,如果参数是数组，则匹配一个即可,如 "*.domain.com,www.domain.com"
            'ip'            =>'*',   //IP表达式如： "133.14.11.[1-28],133.22.22.222,111.234.222.*,133.14.11.33/34/38"
            'env'           =>'*',   //可以是数组或者逗号隔开的 环境名 列表如 “dev,test”

            'httpMethod'    =>'*',   //可以是数组或者逗号隔开的 HTTP方法 列表如 “GET,POST”
            'requestType'   =>'*',   //可以是数组或者逗号隔开的 请求类型 列表如： “api,web,cli,ajax,jsonp”
            'entryName'     =>'*',   //可以是数组或者逗号隔开的 入口名 列表如 home,api,admin
            'moduleName'    =>'*',   //可以是数组或者逗号隔开的 入口名 列表如 www,api,admin

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
     * return bool 是否通过验证器检查
     */
    public function isMatch($requestFilter){
        //空规则, * ,all 表示任意 直接通过
        if(empty($requestFilter) || (is_string($requestFilter) && in_array($requestFilter, array('*', 'all'))) ){
            return true;
        }
        foreach($requestFilter as $filterName=>$filterArg){
            //skip empty config
            if(is_numeric($filterName) && empty($filterArg)){continue;}
            //无键名的配置项目 按 array(filterName, filterCfg) 结构解释
            if(is_numeric($filterName)){
                $filterName = $filterArg[0];
                $filterArg  = @$filterArg[1];
            }

            //如果过滤器名，是以 ! 开头 表示这是一个反过滤器，进行 反操作验证
            $firstChar = substr($filterName, 0, 1);
            $isNot = false;
            if($firstChar==='!'){
                $filterName = substr($filterName, 1);
                $isNot = true;
            }

            //当配置值为 * 时不进行任何检查
            if(is_string($filterArg) && $filterArg==='*'){
                $filterChk = true;
            }else{
                //使用特定的过滤器进行检查
                $filterMethod = '__rcf__'.$filterName;
                if(!method_exists($this, $filterMethod)){
                    throw new CspException("Error filter config for $filterName : ".json_encode($filterArg));
                }
                $filterChk = $this->$filterMethod($filterArg);
            }

            if( ($isNot===false && !$filterChk) || ($isNot===true  && $filterChk) ){
                return false;
            }
        }
        return true;
    }

    /**
     * 检查当前的请求域名
     *
     * @param $filterArg string 配置项可以是 逗号隔开的 域名列表，或者数组，可以使用通配符如  *.abc.com
     * @return bool
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
    private function __rcf__host($filterArg){
        return self::__rcf__domain($filterArg);
    }

    /**
     * IP 过滤器，符合条件时返回 true
     * @param $filterArg string 为IP 列表表达式如： "133.14.11.[1-28],133.22.22.222,111.234.222.*,133.14.11.33/34/38";
     * @return bool
     */
    private function __rcf__ip($filterArg){
        return $this->isInIpList($filterArg, $this->getClientIp());
    }

    /**
     * requestType 过滤器
     *
     * @param $filterArg string 配置值可以是 逗号隔开的 requestType，或者数组 如 “api,jsonp”
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
     * @param $filterArg string 配置值可以是 逗号隔开的 入口名，或者数组 如 “home,admin”
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
    }

    /**
     * 模块过滤器
     *
     * 检查请求是否从特定的入口进入，必须先在入口文件中配置 CSPHP_ENTRYNAME 常量
     *
     * @param $filterArg string 配置值可以是 逗号隔开的 入口名，或者数组 如 “home,admin”
     * @return bool
     * @throws \Csp\core\CspException
     */
    private function __rcf__moduleName($filterArg){
        $mName = Csphp::getModuleName();
        if($mName){
            if(!is_array($filterArg)){
                $filterArg = explode(',', $filterArg);
            }
            return in_array($mName, $filterArg);
        }else{
            throw new CspException("Use  moduleName filter , pls define CSPHP_ENTRYNAME const ");
        }
    }

    /**
     * 运行环境过滤器
     * 检查当前请求是否在 特定的运行环境中，必须先在入口文件中配置 CSPHP_ENV_TYPE 常量
     *
     * @param $filterArg string 配置值可以是 逗号隔开的 环境名，或者数组 如 “dev,test,prod”
     * @return bool
     * @throws \Csp\core\CspException
     */
    private function __rcf__env($filterArg){
        if(defined('CSPHP_ENV_TYPE')){
            if(!is_array($filterArg)){
                $filterArg = explode(',', strtolower($filterArg));
            }
            return in_array(CSPHP_ENV_TYPE, $filterArg);
        }else{
            throw new CspException("Use  env filter , pls define CSPHP_ENV_TYPE const ");
        }
    }


    /**
     * httpMethod 过滤器
     * 配置值可以是 逗号隔开的 HTTP方法值，或者数组 如 “GET，POST” or array("GET","DELETE")
     *
     * @param $filterArg string
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
     * @param $filterArg string 可以是一个 逗号隔开的前缀列表，或者 数组
     * @return bool
     */
    private function __rcf__urlPrefix($filterArg){
        $reqRoute = $this->getReqRoute();
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
     * @param $filterArg string 可以是一个 逗号隔开的后缀列表，或者 数组
     * @return bool
     */
    private function __rcf__urlSuffix($filterArg){
        $reqRoute = $this->getReqRoute();
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
     * @param $fileArg  string 可以是用逗号隔开的 路由列表，或者数组，
     *      如 “user/*,post/create” 或者 array("user/*", "post/create")
     * @return bool
     */
    private function __rcf__match($fileArg){
        return $this->isRouteInlist($this->getReqRoute(), $fileArg);
    }

    /**
     * 正则过滤器
     * match 正刚过滤器的配置项为 , 被检查的是当前请求路由 不包含前后 /
     * @param $fileArg string 正则表达式,或者正则表达式数组，符合一条即可
     * @return bool
     */
    private function __rcf__regexp($fileArg){
        if(!is_array($fileArg)){
            $fileArg = array($fileArg);
        }
        $route = $this->getReqRoute();
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
     * @param $filterArg array 配置规则见 self::__rcf_chkValue
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
     * @param $vr               string 变量路由
     * @param null $valOrRule   string 变量值 或者 规则
     * @param string $op        string 操作符可能是 == != >= <= > < regexp any ci
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
    }
    /**
     * 用户自定义的过滤器
     *
     * @param $filterArg callable 闭包或者 callable callback 如： function (CspRequest){}
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

    }
    /**
     * 注册请求检查器，用于查找符合条件的 请求，可配置在 reqCondFilter 的 userFounder 字段
     * @param $founderName  string
     * @param $founderFunc  callable 查找逻辑的 闭包 或者  callable ，
     */
    public function registerFounder($founderName, $founderFunc){
        self::$reqFounderFunc[$founderName] = $founderFunc;
    }

    /**
     * 检查是否在IP列表
     * @param string $ipList = "133.14.11.[1-28],133.22.22.222,111.234.222.*,133.14.11.33/34/38";
     * @param string $ip  被检查的 IP 或者自动从系统取
     * @return bool
     */
    public function isInIpList($ipList, $ip=null){
        $ip = $ip===null ?  $this->getClientIp() : $ip;
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
     * @param $routeName    string
     * @param $list         string 逗号隔开的 路由列表，或者数组，每一项将使用 fnmatch 检查
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
Csp::request()->getReqUri();
Csp::request()->getReqRoute();
Csp::request()->getLastViewUrl();//用户最后一次浏览的 url

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