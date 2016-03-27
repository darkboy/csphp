<?php
/**
 *
 */
/**
 * Class Csphp core mgr class
 */
use Csp\core\CspRequest     as CspRequest;
use Csp\core\CspResponse    as CspResponse;
use Csp\core\CspLog         as CspLog;
use Csp\core\CspEvent       as CspEvent;
use Csp\core\CspRouter      as CspRouter;
use Csp\core\CspTemplate    as CspTemplate;
use Csp\core\CspValidator   as CspValidator;
use Csp\core\CspException   as CspException;

//设置当前的运行环境
defined('CSPHP_ENV_TYPE') or define('CSPHP_ENV_TYPE', Csphp::ENV_TYPE_PROD);

class Csphp {
    const ENV_TYPE_PROD = 'prod';
    const ENV_TYPE_TEST = 'test';
    const ENV_TYPE_DEV  = 'dev';
    public static $appStartTime = 0;
    /**
     * @var Csphp
     */
    public static $app = null;
    /**
     * @var csp core root path
     */
    public static $coreRootPath = null;

    /**
     * 应用配置
     * @var array
     */
    public static $appCfg = null;
    /**
     * 系统配置
     * @var array
     */
    public static $sysCfg = null;

    //挂载的 核心 对象
    private $coreObjs = array(
        'request'   =>null,
        'response'  =>null,
        'log'       =>null,
        'validator' =>null,
    );

    /**
     * 系统别名 字典，可以在配置路径的时候，使用 @aliasname 代表相应的目录
     * @var array
     */
    public static $aliasPathMap = array();
    /**
     * 组件对象
     * @var array
     */
    private static $componentObjs = array();
    public function __construct($appCfg=null){

        $this->initConfig($appCfg);

    }

    /**
     * 初始化应用 与 系统配置
     * @param $appCfg
     */
    private function initConfig($appCfg){
        self::$appStartTime = microtime(true);

        if($appCfg!=null && is_array($appCfg)){
            self::$appCfg = $appCfg;
        }

        self::$coreRootPath = dirname(__FILE__);
        require_once self::$coreRootPath.'/CsphpAutoload.php';

        //load sys config
        self::$sysCfg = include(self::$coreRootPath.'/CspCfg.php');
        //ower write system config
        if(isset(self::$appCfg['system_config_over_write']) && is_array(self::$appCfg['system_config_over_write'])){
            foreach(self::$appCfg['system_config_over_write'] as $k=>$v){
                self::$sysCfg[$k] = $v;
            }
        }

        self::initAliasPathMap();
    }

    /**
     * 初始化系统路径别名,所有路径 不以 / 结尾
     */
    private static function initAliasPathMap(){
        $appRoot = self::appCfg('app_base_path');
        $sysRoot = self::sysCfg('system_base_path');
        self::$aliasPathMap['@app'] = $appRoot;
        self::$aliasPathMap['@sys'] = $sysRoot;

        self::$aliasPathMap['@comp']    = $appRoot.'/components';
        self::$aliasPathMap['@cfg']     = $appRoot.'/config';
        self::$aliasPathMap['@ctrl']    = $appRoot.'/controlers';
        self::$aliasPathMap['@ext']     = $appRoot.'/exts';
        self::$aliasPathMap['@view']    = $appRoot.'/views';
        self::$aliasPathMap['@tpl']     = $appRoot.'/views';
        self::$aliasPathMap['@mod']     = $appRoot.'/models';
        self::$aliasPathMap['@pub']     = $appRoot.'/../public';
        self::$aliasPathMap['@log']     = $appRoot.'/var/log';
        self::$aliasPathMap['@upload']  = $appRoot.'/../public/upload';

        self::$aliasPathMap['@f-comp']  = $sysRoot.'/comp';
        self::$aliasPathMap['@f-ext']   = $sysRoot.'/ext';

        //把用户定义的路径加载进来, 可以覆盖以上的内容路径
        foreach(self::$appCfg['alias_path_config'] as $aliasName=>$pathTpl){
            self::$aliasPathMap[$aliasName] = self::getPathByRoute($pathTpl);
        }
    }

    /**
     * start a applatection
     */
    public function run(){
        //初始化核心对象
        $this->initCoreObjs();
        $this->registerShutDown();
        self::tmp();
    }

    /**
     * 初始化所有的组件
     */
    public function initComponentChain(){

        foreach(self::$sysCfg['components'] as $compCfg){

        }

        foreach(self::$appCfg['components'] as $compCfg){

        }
    }

    /**
     * 传递一个参数时，为直接引用对象，传弟2-3个参数时，为初始化组件
     * Csphp::comp($comRoute, $cfg, $accessKey='')->anyMethod();
     * $cfg['components']=array(
            //这个key是访问名称
            'access_key'=> array(
                'filter'=>$requestFilter,//什么条件下加载组件
                //对象定位路由,可以定位组件对象
                'comp'  =>$fRoute,
                //组合配置字典 中的每一项都将设置为组件的属性 即 comp->cfgname = $value
                'cfg'   =>array(
                    'cfgname'=>$value
                )
        )
        );
     */
    public static function comp($comRoute, $cfg=null, $accessKey=null){
        if(func_num_args()==1){
            if(isset(self::$componentObjs[$comRoute])){

            }else{
                return self::$componentObjs[$comRoute];
            }
        }else{
            if($accessKey===null){
                $accessKey = $comRoute;
            }

        }
    }

    /**
     * 通过对象路由表达式，获取路径
     * @param $oRoute
     * 类文件 与 对象 别名定位规则,如能是由下值之一
     *      @view/user/index.php 使用别名，可用的 别名 见 self::initAliasPathMap
     *      /user/abc   linux 下的绝对路径
     *      c:\\xxxx    windows 下的 绝对路径
     *      user/index  默认为 app 路径下
     */
    public static function getPathByRoute($oRoute){
        $oRoute = trim($oRoute);
        $oRoute = rtrim($oRoute, '/');

        $firstChar = substr($oRoute,0,1);
        $rs = explode('/', $oRoute);

        if($firstChar==='@'){
            $rs[0] = self::getAliasPath($rs[0]);
            return join(DIRECTORY_SEPARATOR, $rs);
        }else{
            if($firstChar=='/' || substr($oRoute,1,1)===':'){
                return $oRoute;
            }else{
                return self::appCfg('app_base_path').'/'.$oRoute;
            }
        }
    }

    /**
     * 通过对象路由表达式，获取命名空间 或者 类名
     * @param $oRoute
     * 类文件 与 对象 别名定位规则,如能是由下值之一
     *      @view/user/index.php 使用别名，可用的 别名 见 self::initAliasPathMap
     *      /user/abc   linux 下的绝对路径
     *      c:\\xxxx    windows 下的 绝对路径
     *      user/index  默认为 app 路径下
     */
    public static function getNamespaceByRoute($oRoute){
        $oRoute = trim($oRoute);
        $oRoute = rtrim($oRoute, '/\\');

        $firstChar = substr($oRoute,0,1);
        if($firstChar==='@'){
            $rs = explode('\\', $oRoute);
            $rs[0] = self::getAliasNamespace($rs[0]);
            return join('\\', $rs);
        }else{
            if($firstChar=='\\'){
                return $oRoute;
            }else{
                return self::appCfg('app_namespace').'\\'.$oRoute;
            }
        }
    }


        /**
     * 返回 别名 的实际路径
     * @param $aliasName 以 @ 开头的别名字符串
     */
    public static function getAliasPath($aliasName){
        $aliasName = trim($aliasName);
        return self::$aliasPathMap[$aliasName];
    }

    /**
     * 获取别名 对应的 命名空间 前缀
     * @param $aliasName
     * @return mixed
     */
    public static function getAliasNamespace($aliasName){
        $aliasName = trim($aliasName);
        return self::$aliasPathMap[$aliasName];
    }

    /**
     * 加载一个文件
     * @param $oRoute   可以包含别名前缀
     * @return mixed
     */
    public static function loadFile($oRoute){
        $realRoute = self::getPathByRoute($oRoute);
        if(!strtolower(substr($realRoute,-4))===".php"){
            $realRoute.='.php';
        }
        if(!file_exists($realRoute)){
            throw new CspException("Can not load file , Error fRoute  $oRoute after parse is  : ".$realRoute);
        }
        return require($realRoute);
    }

    /**
     * 实例化一个对象
     * @param $oRoute
     * @param null $cfg
     */
    public static function newClass($oRoute, $cfg=null, $isSingleton=true){
        static $objs = array();
        if($isSingleton && isset($objs[$oRoute])){
            return $objs[$oRoute];
        }
        $realNamespace = self::getPathByRoute($oRoute);

    }
    public static function ctrl($route, $cfg=null, $isSingleton=true){
        $route = ltrim($route, ' /');
        return self::newClass('@ctrl/'.$route, $cfg, $isSingleton);
    }
    public static function mod($route, $cfg=null, $isSingleton=true){
        $route = ltrim($route, ' /');
        return self::newClass('@mod/'.$route, $cfg, $isSingleton);
    }
    public static function ext($route, $cfg=null, $isSingleton=true){
        $route = ltrim($route, ' /');
        return self::newClass('@ext/'.$route, $cfg, $isSingleton);
    }

    /**
     * 应用退出
     */
    public static function exitApp(){
        self::app()->response()->send();
    }



    /**
     * 统一规范 rest 接口，ajax ,以及 jsonp 接口 数据格式
     * @param $rst
     * @param int $code
     * @param string $msg
     * @param string $tips
     * @return string jsonString
     */
    public static function warpJsonApiData($rst, $code=0, $msg='OK', $tips=''){
        //header('Content-type: application/json');
        $r = array(
            'status'=>array(
                'code'  =>$code*1,
                'msg'   =>$msg,
                'tips'  =>($tips ? $tips : $msg)
            ),
            'data'=>$rst
        );
        //APP::trace();
        if(self::isDebug() && !self::isCli()){
            $r['status']= array_merge($r['status'], array(
                'time'  =>sprintf("%.4f Sec",self::getTimeUse()),
                'sip'   =>$_SERVER['SERVER_ADDR'],
                'cip'   =>self::request()->clientIp()
            ));
        }
        return json_encode($r);
    }
    /**
     *init core objs
     */
    private function initCoreObjs(){
        $this->coreObjs['request']  = new CspRequest();
        $this->coreObjs['response'] = new CspResponse();
        $this->coreObjs['log']      = new CspLog();
        $this->coreObjs['tpl']      = new CspTemplate();
        $this->coreObjs['validator']= new CspValidator();
    }
    private function registerShutDown(){
    }
    /**
     * @param $appConfig
     */
    public static function createApp($appConfig=array()){
        self::$app = new self($appConfig);
        return self::$app;
    }

    /**
     * @return Csphp
     */
    public static function app(){
        return self::$app;
    }
    /**
     * @return CspRequest
     */
    public function request(){
        return $this->coreObjs['request'];
    }
    /**
     * @return CspResponse
     */
    public function response(){
        return $this->coreObjs['response'];
    }
    /**
     * @return CspRouter
     */
    public function router(){
        return $this->request()->router();
    }
    /**
     * @return CspValidator
     */
    public function validator(){
        return $this->coreObjs['validator'];
    }

    /**
     * @return CspLog
     */
    public function log(){
        return $this->coreObjs['log'];
    }

    /**
     * @return CspTemplate
     */
    public function tpl(){
        return $this->coreObjs['tpl'];
    }

    /**
     * @param $vr //vr变量路由 规则如下
     * $vr='-:a/b/c';//$sysCfg[a][b][c]
     * $vr='s:a/b/c';//$_SERVER
     * $vr='e:a/b/c';//$_ENV
     * $vr='g:a/b/c';//$_GET
     * $vr='p:a/b/c';//$_POST
     * $vr='c:a/b/c';//$_COOKIE
     * $vr='r:a/b/c';//$_REQUEST;
     * $vr='f:a/b/c';//$_FILES;
     * $vr='v:a/b/c';//var from route parse
     * $vr='h:a/b/c';//header
     * $vr='a:a/b/c';// value in v or r
     *
     * @param null $def
     * @param string $rule
     * @param string $tips
     * @param null $errHandle
     */
    public static function V($vr, $def=null, $rule='', $tips='', $errHandle=null){
        //存储已经获取过的值
        static $vCache      = array();
        static $inputCache  = array();


        $vr = trim($vr, ' /');

        if(isset($vCache[$vr])){
            return $vCache;
        }

        //检查变更路由 的合法性
        if ( !preg_match("#^([gpcrfsehv-])(?::(.+))?\$#sim",$vr,$m) ){
            throw new CspException("Can't parse var from vRoute: {$vr} ");
            return NULL;
        }

        //提取变量的类型 即第一个字符
        $vType = strtoupper(substr($vr,0,1));
        //提取变量的路径，即第3个字符之后
        $vPath= substr($vr,2);
        $vKes = explode('/', $vPath);
        //初始化所有输入
        if(empty($inputCache)){
            $inputCache = array(
                'C'=>&$_COOKIE, 'G'=>&$_GET,	'P'=>&$_POST,'R'=>&$_REQUEST,
                'F'=>&$_FILES,	'S'=>&$_SERVER,	'E'=>&$_ENV,
                '-'=>&self::$appCfg
            );
        }

        if ($vType==='R' && empty($inputCache['R'])) {
            $inputCache['R'] = array_merge($_COOKIE, $_GET, $_POST);
        }

        if($vType==='H' && !isset($inputCache['H'])){
            $inputCache['H'] = http_get_request_headers();
        }

        $v  = null;
        while ( $fk = array_shift($vKes) ) {
            if($v==null){
                if( isset($inputCache[$vType][$fk]) ){
                    $v = $inputCache[$vType][$fk];
                }else{
                    return $def;
                }
            }else{
                if(isset($v[$fk])){
                    $v = $v[$fk];
                }else{
                    return $def;
                }
            }
        }

        //没有验证规则，直接缓存后返回
        if(empty($rule)){
            $vCache[$vr] = $v;
            return $v;
        }

        //验证检查 缓存 返回
        if( CspValidator::validate($v, $rule) ){
            $vCache[$vr] = $v;
            return $v;
        }else{
            $voidInfo = CspValidator::getVoidInfo($vPath, $tips);
            if($errHandle){
                if(!is_callable($errHandle)){
                    throw new CspException('errHandle is not callable ', 10000);
                }
                call_user_func($errHandle, $voidInfo);
            }else{

                throw new CspException($voidInfo['tips'], $voidInfo['code']*1);
            }
            return null;
        }
    }

    /**
     * URL 构造器
     * @param $r
     * @param $paramArrOrStr
     * @param $anchor
     * @param string $hostKey
     * @return string url
     */
    public static function url($r, $paramArrOrStr, $anchor, $hostKey='_default'){

    }
    public static function coreError($msg){
        self::trace();
        echo htmlspecialchars($msg);
        throw new CspException($msg, 10000);

        //trigger_error($msg, E_USER_ERROR);
    }

    /**
     * 判断当前是否运行在生产环境
     * @return bool
     */
    public static function isProdEnv(){
        return CSPHP_ENV_TYPE === self::ENV_TYPE_PROD;
    }
    /**
     * 判断当前是否运行在测试环境
     * @return bool
     */
    public static function isTestEnv(){
        return CSPHP_ENV_TYPE === self::ENV_TYPE_TEST;
    }
    /**
     * 判断当前是否运行在开发环境
     * @return bool
     */
    public static function isDevEnv(){
        return CSPHP_ENV_TYPE === self::ENV_TYPE_DEV;
    }

    /**
     * 判断当前是否运行在 CLI 环境
     * @return bool
     */
    public static function isCli(){
        return strtolower(PHP_SAPI)==='cli';
    }

    public static function isDebug(){
        return true;
    }

    /**
     * 四种日志记录 Debug info warning error
     * @param $type 错误的类型，用于分类错误信息
     * @param $msg
     * @param $context  上下文字典信息，$msg 中的 {{keyname}} 会替换为 $context[keyname]
     */
    public static function logDebug($type, $msg, $context=null){
        self::log()->logDebug($type, $msg, $context);
    }
    public static function logInfo($type, $msg, $context=null){
        self::log()->logInfo($type, $msg, $context);
    }
    public static function logWarning($type,$msg, $context=null){
        self::log()->logWarning($type, $msg, $context);
    }
    public static function logError($type, $msg, $context=null){
        self::log()->logError($type, $msg, $context);
    }


    /**
     * @return mixed
     */
    public static function getTimeUse(){
        return microtime(true) - self::$appStartTime;
    }
    //调试方法
    public static function trace($traceInfo=null){
        if(self::isProdEnv()){
            //echo "Can not print trace in prodution env!";exit;
        }
        //debug_print_backtrace();
        $traces = $traceInfo ? $traceInfo : debug_backtrace();
        $traceString  = '';
        foreach ($traces as $key=>$trace) {
            //代码跟踪级别限制
            if ($key > 20) {break;}
            $argsString = ($trace['args'] && is_array($trace['args'])) ? '(' . json_encode($trace['args']) . ')': '()';
            $argsString = substr($argsString,0,200);
            $f   = isset($trace['file']) ? "....".substr($trace['file'],-20) : str_repeat(".",24);
            $cls = isset($trace['class']) ? $trace['class'] : '';
            $type = isset($trace['type']) ? $trace['type'] : '';
            $line = sprintf("%3s",@$trace['line']);
            $key  = sprintf("%02d",$key);
            $traceString .= "#{$key}#  {$f}(Line:{$line}) {$cls}{$type}{$trace['function']}{$argsString}\n";
        }
        $line = self::isCli() ? str_repeat("-",80)."" : "<hr><pre>";
        echo "\nTraceInfo is:\n",$line,"\n",$traceString,$line,"\n";
    }

    /**
     * 美化输出格式，用于调试
     * @param null $data
     * @param bool $isVarDump
     */
    public static function dump($data = null, $isVarDump = false, $isExit=true) {

        $pre = "\n".str_repeat("-",80)."\n";
        $preEnd=str_repeat("-",80)."\n";
        //设置页面编码
        if (!self::isCli() && !headers_sent()) {
            header("Content-Type:text/html; charset=utf-8");
        }
        if(!self::isCli()){
            $pre = "<pre><hr>";
            $preEnd="</pre>";
        }

        //当print_r输出内容时 不作处理
        if(!$isVarDump){
            echo $pre,print_r($data,1),$preEnd;
        } else {
            ob_start();
            var_dump($data);
            $output = ob_get_clean();

            $output = str_replace('"', '', $output);
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

            echo $pre, $output, $preEnd;
        }

        if ($isExit) exit();
    }

    /**
     * get system cfg
     * @param $k
     * @param null $def
     */
    public static function sysCfg($k, $def=null){
        $vKeys = explode('/', $k);
        return isset(self::$sysCfg[$k]) ? self::$sysCfg[$k] : $def ;
    }
    /**
     * get app cfg
     * @param $k
     * @param null $def
     */
    public static function appCfg($k, $def=null){
        return self::V('-:'.$k, $def);
    }
    /**
     * set config in runtime
     * @param $k
     * @param $v
     */
    public static function setAppCfg($k, $v){
        self::$appCfg[$k]=$v;
    }
    /**
     * set config in runtime
     * @param $k
     * @param $v
     */
    public static function setSysCfg($k, $v){
        self::$sysCfg[$k] = $v;
    }


    /**
     * 触发一个事件
     * @param $eventName        事件名
     * @param null $data        事件相关的数据
     * @param null $senderObj   事件发送者，对象
     */
    public static function fireEvent($eventName, $data=null, $senderObj=null){
        CspEvent::fire($eventName, $data, $senderObj);
    }

    /**
     * 监听某个事件
     * @param $eventName                需要监听的事件名
     * @param $eventListenerCallback    事件处理器，func($eventDdata, eventSender=null){}
     */
    public static function on($eventName, $eventListenerCallback){
        CspEvent::on($eventName, $eventListenerCallback);
    }


    /**
     * alias for self::on
     * 监听某个事件
     * @param $eventName                需要监听的事件名
     * @param $eventListenerCallback    事件处理器，func($eventDdata, eventSender=null){}
     */
    public static function listen($eventName, $eventListenerCallback){
        CspEvent::on($eventName, $eventListenerCallback);
    }

    public function cls(){}


    public static function tmp(){
        echo '<pre>';print_r($_SERVER);
    }
}

