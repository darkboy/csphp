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
defined('CSPPHP_ENV_TYPE') or define('CSPPHP_ENV_TYPE', Csphp::ENV_TYPE_PROD);

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

    public static $appCfg = null;
    public static $sysCfg = null;

    //挂载的 核心 对象
    private $coreObjs = array(
        'request'   =>null,
        'response'  =>null,
        'route'     =>null,
        'log'       =>null,
        'event'     =>null,
        'validator' =>null,
    );
    public function __construct($appCfg=null){

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
    }
    /**
     * start a applatection
     */
    public function run(){
        $this->initCoreObjs();
        $this->registerShutDown();
        self::tmp();
    }

    public static function exitApp(){
        self::app()->response()->send();
    }

    public static function tmp(){
        $v = Csphp::V('-:demo_key1/demo_key2','', 'num:1-3');
        self::dump($v);
    }


    /**
     * 统一规范 rest 接口，ajax ,以及 jsonp 接口 数据格式
     * @param $rst
     * @param int $code
     * @param $msg
     * @param string $tips
     * @return string
     */
    public static function warpJsonApiData($rst, $code=0, $msg, $tips=''){
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
        $this->coreObjs['router']   = new CspRouter();
        $this->coreObjs['log']      = new CspLog();
        $this->coreObjs['validator']= new CspValidator();
        $this->coreObjs['tpl']      = new CspTemplate();
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
        return $this->coreObjs['router'];
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
        //所有输入
        $inputCache = array(
            'C'=>&$_COOKIE, 'G'=>&$_GET,	'P'=>&$_POST,'R'=>&$_REQUEST,
            'F'=>&$_FILES,	'S'=>&$_SERVER,	'E'=>&$_ENV,
            '-'=>&self::$appCfg
        );
        if (empty($inputCache['R'])) {
            $inputCache['R'] = array_merge($_COOKIE, $_GET, $_POST);
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
        return CSPPHP_ENV_TYPE === self::ENV_TYPE_PROD;
    }
    /**
     * 判断当前是否运行在测试环境
     * @return bool
     */
    public static function isTestEnv(){
        return CSPPHP_ENV_TYPE === self::ENV_TYPE_TEST;
    }
    /**
     * 判断当前是否运行在开发环境
     * @return bool
     */
    public static function isDevEnv(){
        return CSPPHP_ENV_TYPE === self::ENV_TYPE_DEV;
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

    }

    public function cls(){}
}

