<?php
/**
 *
 */
/**
 * Class Csphp core mgr class
 */
use Csp\core\CspRequest;
use Csp\core\CspResponse;
use Csp\core\CspLog;
use Csp\core\CspEvent;
use Csp\core\CspRouter;
use Csp\core\CspCliConsole;
use Csp\core\CspTemplate;
use Csp\core\CspValidator;
use Csp\core\CspException;
use Csp\CsphpAutoload;


//设置当前的运行环境
defined('CSPHP_ENV_TYPE') or define('CSPHP_ENV_TYPE', Csphp::ENV_TYPE_PROD);

class Csphp {
    /**
     * 生产环境
     */
    const ENV_TYPE_PROD = 'prod';
    /**
     * 测试环境
     */
    const ENV_TYPE_TEST = 'test';
    /**
     * 开发环境
     */
    const ENV_TYPE_DEV  = 'dev';


    /**
     * 以下是所有的 系统内置 事件列表
     * 组件 和 应用 可以在不同 流程 注入逻辑（监听相应的事件）
     * 通过变更  app response request route tpl 进行 数据更改 和 流程干预
     */
    /**
     * 核心架构 准备完毕事件，已完成 配置，初始化
     */
    const EVENT_CORE_AFTER_INIT         = 'event.core.init';

    /**
     * 路由解释 结束
     */
    const EVENT_CORE_AFTER_ROUTE        = 'event.core.route.alfter';

    /**
     * 用户以 及 系统配置的 组件初始化完成，即：已调用完他们的 start 方法
     */
    const EVENT_CORE_AFTER_COMP_INIT    = 'event.core.after.comp.init';

    /**
     * 系统准备开始 执行 Action请求运作
     */
    const EVENT_CORE_BEFORE_ACTION      = 'event.core.before.action';


    /**
     * 开始渲染一个模板，可能被触发多次
     */
    const EVENT_CORE_BEFORE_TPL_RENDER  = 'event.core.before.tpl.render';
    /**
     * 结束渲染一个模板，可能被触发多次
     */
    const EVENT_CORE_AFTER_TPL_RENDER   = 'event.core.after.tpl.render';


    /**
     * 系统结束了action动作
     */
    const EVENT_CORE_AFTER_ACTION       = 'event.core.after.action';


    /**
     * 所有数据准备就续，准备发送
     */
    const EVENT_CORE_BEFORE_SEND_RESP   = 'event.core.before.send.resp';


    /**
     * 所有数据发送完毕
     */
    const EVENT_CORE_AFTER_SEND_RESP    = 'event.core.after.send.resp';

    /**
     * 应用退出，即将结束PHP 进程
     */
    const EVENT_CORE_EXIT               = 'envent.core.app.exit';


    /**
     * @var Csphp
     */
    public static $app = null;

    /**
     * 应用开始时间
     * @var int
     */
    public static $appStartTime = 0;

    /**
     * @var string, core root path
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

    /*
     * 保存 核心 对象 的集合
     * @var array
     */
    private static $coreContainer = array(
        'request'   =>null,
        'router'    =>null,
        'response'  =>null,
        'cliConsole'=>null,
        'log'       =>null,
        'validator' =>null,
    );

    /**
     * 当前运行的模块数据
     * @var array
     */
    public  static $curModule = array();

    /**
     * 系统别名 字典，可以在配置路径 或者 的时候，使用 @aliasname 代表相应的目录
     * @var array
     */
    private static $aliasMap = array();


    /**
     * 组件单例对象池，所有实例化后的组件存储在这里，access_key=>compObj
     * @var array
     */
    private static $compSingletonObjPool = array();
    /**
     * 组件工厂，所有组件的自定义实例化方法存储在这里 {access_key|class_name}=>{Closure|class_name}
     * @var array
     */
    private static $compFactory = array();


    /**
     * 存储系统的执行标签与时间
     * @var array
     */
    private static $bankmarkData = array();


    /**
     * 当前项目是否在debug模式
     * @var bool
     */
    private static $isDebug     = false;
    /**
     * 当前的运行环境
     * @var string
     */
    private static $runningEnv  = self::ENV_TYPE_PROD;

    /**
     * 应用 对象构造函数
     */
    public function __construct(){
    }

    //------------------------------------------------------------------------------

    /**
     * @param $appConfig array
     */
    public static function createApp($appConfig=array()){
        self::$app = new self();
        return self::$app->initConfig($appConfig);
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

        //注册应用的命名空间
        CsphpAutoload::addNamespace(self::appCfg('app_base_path'), self::appCfg('app_namespace'), '.php');

        //load sys config
        self::$sysCfg = include(self::$coreRootPath.'/CspCfg.php');
        //ower write system config
        if(isset(self::$appCfg['system_config_over_write']) && is_array(self::$appCfg['system_config_over_write'])){
            foreach(self::$appCfg['system_config_over_write'] as $k=>$v){
                self::$sysCfg[$k] = $v;
            }
        }
        return $this;
    }

    /**
     * start applatection
     */
    public function run(){

        //初始化
        self::init();

        //访问控制检查
        self::checkAccessControl();

        //处理请求
        self::handlerRequest();

        //self::tmp();
        //退出程序
        self::exitApp();
    }

    /**
     * 应用退出，如果应用没有显式的调用 也会在 shutdown 时自动触发
     */
    public static function exitApp(){
        static $isExit = false;
        if($isExit){return true;}

        self::fireEvent(self::EVENT_CORE_BEFORE_SEND_RESP);
        self::response()->send();
        self::fireEvent(self::EVENT_CORE_AFTER_SEND_RESP);
        //提前断开 客户端连接
        if(function_exists('fastcgi_finish_request')){
            fastcgi_finish_request();//echo 'fastcgi_finish_request';
        }
        self::log()->logInfo('exitCsphp on '.date("Y-m-d H:i:s"), $_REQUEST, 'access')->flush();
        $isExit=true;
        self::fireEvent(self::EVENT_CORE_EXIT);
        exit();
    }
    //------------------------------------------------------------------------------

    /**
     * 应用初始化,
     */
    public static function init(){

        //当应用没有显式的调用 exitApp 方法时，在 shutdown 时自动触发
        register_shutdown_function(array(__CLASS__,'exitApp'));

        //初始化运行环境，设置调试模式
        self::initRunningEvn();

        //初始化核心对象
        self::initCoreObjs();

        //初始化路径别名，命名空间别名 配置 @mod @ctrl @view 等
        self::initAliasMap();

        //初始化当前模块配置，从 modules 配置中 查找符合当前请求的模块
        self::initModule();

        //初始化组件，从 components 配置中 绑定类的生产脚本，以及预初始化相关组件
        self::initComponents();

        //导入 helpers，自动加载， helpers/*.preload.php 与 helpers/*/*.preload.php
        self::loadHelperFiles();

        //系统初始化结束，触发 EVENT_CORE_AFTER_INIT 事件，在前面预加载的类可以监听这个事件
        self::fireEvent(self::EVENT_CORE_AFTER_INIT);

        return true;
    }

    /**
     * 系统的访问控制检查
     *
     * @param null $aclCfg
     *
     * @return bool
     */
    public static function checkAccessControl($aclCfg=null){
        if($aclCfg===null){
            $aclCfg = self::appCfg('acl', array());
        }
        if(empty($aclCfg)){
            return true;
        }
        foreach($aclCfg as $acl){
            if(empty($acl)){continue;}
            //todo..

        }
        return true;
    }

    /**
     * 处理请求
     */
    public static function handlerRequest(){
        //cli 与 http 请求分别进行路由 和 初始化动作
        if(self::isCli()){
            self::handlerCliRequest();
        }else{
            self::handlerWebRequest();
        }

        return true;
    }

    /**
     * 处理 CLI 请求
     * @return bool
     */
    protected static function handlerCliRequest(){

        //解释路由信息
        self::cliConsole()->parseRoute();
        //执行命令行动作
        self::cliConsole()->doAction();

        return true;
    }

    /**
     * 处理 WEB 请求
     * @throws \Csp\core\CspException
     */
    protected static function handlerWebRequest(){

        //解释路由信息
        self::router()->parseRoute();
        //self::router()->dump();
        //执行控制器动作
        self::router()->doAction();

        return true;
    }

    //------------------------------------------------------------------------------

    /**
     * 初始化运行环境,
     */
    private static function initRunningEvn(){
        self::$runningEnv = defined('CSPHP_ENV_TYPE') ? CSPHP_ENV_TYPE : false;
        $isDebug = defined('CSPHP_IS_DEBUG') ? CSPHP_IS_DEBUG : false;
        self::setDebug($isDebug);
    }


    /**
     * 初始化系统路径别名,所有路径 不以 / 结尾， 命名空间不以 \ 结尾
     * 配置格式为:
     * aliasname=>array(pathPrefix,nsPrefix)
     */
    private static function initAliasMap(){
        $appRoot = self::appCfg('app_base_path');
        $sysRoot = self::sysCfg('system_base_path');
        $appNs   = self::appCfg('app_namespace');

        //echo '<pre>';print_r($appNs);exit;

        self::$aliasMap['@app'] = array($appRoot,$appNs);
        self::$aliasMap['@sys'] = array($sysRoot,'\\Csp');

        self::$aliasMap['@comp']    = array($appRoot.'/components', $appNs.'\\components');
        self::$aliasMap['@cfg']     = array($appRoot.'/config', $appNs.'\\config');
        self::$aliasMap['@ctrl']    = array($appRoot.'/controlers', $appNs.'\\controlers');

        self::$aliasMap['@helper']  = array($appRoot.'/helpers', $appNs.'\\helpers');
        self::$aliasMap['@view']    = array($appRoot.'/views',$appNs.'\\views');
        self::$aliasMap['@tpl']     = array($appRoot.'/views',$appNs.'\\views');
        self::$aliasMap['@mod']     = array($appRoot.'/models',$appNs.'\\models');
        self::$aliasMap['@pub']     = array(dirname($appRoot).'/public', $appNs);
        self::$aliasMap['@log']     = array($appRoot.'/var/log', $appNs);
        self::$aliasMap['@upload']  = array(dirname($appRoot).'/public/upload', $appNs);

        self::$aliasMap['@f-comp']  = array($sysRoot.'/comp','Csp\\comp');
        self::$aliasMap['@f-res']  = array($sysRoot.'/resources','Csp\\resources');

        //把用户定义的路径别名加载进来, 可以覆盖以上的内容路径
        foreach(self::$appCfg['alias_path_config'] as $aliasName=>$v){
            $ns     = is_array($v) ? $v[1] : $appNs;
            $path   = is_array($v) ? $v[0] : $v;
            self::$aliasMap[$aliasName] = [ self::getPathByRoute($path), $ns ];
        }
    }

    /**
     * 模块初始化，检查 当前运行的是什么模块，并将模块信息提取
     */
    private static function initModule(){
        $appRoot = self::appCfg('app_base_path');
        $appNs   = self::appCfg('app_namespace');

        $defaultModule = [];
        foreach(self::appCfg('modules') as $mName=>$m){
            if(empty($defaultModule) && isset($m['is_default']) && $m['is_default']){
                $defaultModule = $m;
            }
            $filter = isset($m['filter']) ?  $m['filter'] : [];

            //可以将常用的 过滤规则直接写在模块配置中 如 host,!host
            foreach(['host','!host','urlPrefix','urlSuffix', 'match','requestType'] as $filterName){
                if(isset($m[$filterName])){
                    $filter[$filterName] = $m[$filterName];
                }
                $filterNameNot = '!'.$filterName;
                if(isset($m[$filterNameNot])){
                    $filter[$filterNameNot] = $m[$filterNameNot];
                }
            }

            //过滤器检查
            if(self::request()->isMatch($filter)){
                self::$curModule = $m;
                break;
            }
        }
        // 模块 查找的优先级为 先找 过滤器 匹配，找不到 就找 home www api 模块
        if(empty(self::$curModule)){
            if(empty($defaultModule)){
                self::$curModule = self::appCfg('modules/home', self::appCfg('modules/www', self::appCfg('modules/api', []) ) );
            }else{
                self::$curModule = $defaultModule;
            }
        }

        $mName = self::$curModule['name'];
        self::$aliasMap['@m-ctrl']  = [$appRoot.'/controlers/'.$mName, $appNs.'\\controlers\\'.$mName];
        self::$aliasMap['@m-view']  = [$appRoot.'/views/'.$mName, $appNs.'\\views\\'.$mName];

        return self::$curModule;
    }

    /**
     * 获取当前模块的信息
     * @return array
     */
    public  static function getModuleInfo(){
        return self::$curModule;
    }

    /**
     * 获取当前模块的名称
     * @return string
     */
    public  static function getModuleName(){
        return self::$curModule['name'];
    }

    /**
     * 获取当前模块的默认路由
     * @return string
     */
    public  static function getModuleDefaultRoute(){
        return '/'.trim(self::$curModule['default_route'],'/');
    }

    /**
     * 自动加载 helpers 中的 *.preload.php 文件 ，扫苗2层目录
     * @throws \Csp\core\CspException
     */
    private static function loadHelperFiles(){
        $helperBasePath = self::appCfg('app_base_path').'/helpers';
        $preloadExt = '*.preload.php';
        $level1Files = glob($helperBasePath.'/'.$preloadExt);
        if(is_array($level1Files) && !empty($level1Files)){
            foreach($level1Files as $f){
                require $f;
            }
        }
        $level2Files = glob($helperBasePath.'/*/'.$preloadExt);
        if(is_array($level2Files) && !empty($level2Files)){
            foreach($level2Files as $f){
                require $f;
            }
        }
    }
    //------------------------------------------------------------------------------


    /**
     * 初始化所有的 组件,
     */
    private static function initComponents(){
        self::initComponentsByCfg( self::appCfg('components', array()) );
        //后加载 sys 组件 ，如果 access_key 有冲突,则以 sys 为准
        self::initComponentsByCfg( self::sysCfg('components', array()) );
        self::fireEvent(self::EVENT_CORE_AFTER_COMP_INIT);
    }

    /**
     * 通过配置数据初始化组件
     *
     * @param $compCfgArr   array ,组件配置数组，如 -:components
     * @return bool
     * @throws \Csp\core\CspException
     */
    private static function initComponentsByCfg($compCfgArr){
        //检查应用中的
        foreach($compCfgArr as $accessKey=>$comp){
            if( isset($comp['pre_init']) && $comp['pre_init'] ) {
                self::createComponent($accessKey, $comp);
            }
        }
        return true;
    }


    /**
     * 生产 或者 获取一个 组件对象，组件的初始化配置 在 components 配置中
     * @param string $accessKey
     * @return  object
     */
    public static function comp($accessKey){
        //已经初始化过的 组件 直接返回
        if(isset(self::$compSingletonObjPool[$accessKey])){
            return self::$compSingletonObjPool[$accessKey];
        }

        $compCfg = null;
        //别名路径
        if($accessKey[0]=='@' || $accessKey[0]==='\\'){
            $accessKey = self::getNamespaceByRoute($accessKey);

        }else{
            //未初始化的组件，尝试查找配置
            $k = 'components/'.$accessKey;
            $compCfg = self::sysCfg($k,  null);
            if(!$compCfg){
                $compCfg = self::appCfg($k, null);
            }
        }

        return self::createComponent($accessKey, $compCfg);

    }

    /**
     *
     * alias for self::comp
     * @param $accessKeyOrClassName
     *
     * @return object
     */
    public static function make($accessKeyOrClassName){
        return self::comp($accessKeyOrClassName);
    }

    /**
     * 创建并初始化一个组件
     *
     * @param string $accessKey
     * @param null $oRoute
     * @param array $opts
     * @param array $filter
     */
    public static function createComponent($accessKey, $compCfg){

        $compClassName = $accessKey;
        if(is_array($compCfg)){
            $compClassName = $compCfg['class'];
            if(is_string($compClassName)){
                $compClassName = self::getNamespaceByRoute($compClassName);
            }
        }

        if(is_string($compClassName) && !class_exists($compClassName)){
            throw new CspException("Error config, can not find component config  or component class: [$accessKey] ");
        }
        if($compClassName instanceof Closure && !is_string($compClassName)){
            throw new CspException("Error config, can not find component config  or component class: [$accessKey] ");
        }

        $compObj = is_string($compClassName) ? new $compClassName : $compClassName();

        //检查并初始化选项
        if(isset($compCfg['options']) && !empty($compCfg['options'])){
            $optionMethod = isset($compCfg['option_method']) ? $compCfg['option_method'] : 'setInitOptions';
            if(!function_exists($compObj, $optionMethod)){
                throw CspException("Cant find comp[".get_class($compObj)."] options method {$optionMethod}");
            }
            $compObj->$optionMethod($compCfg['options']);
        }

        //如果是单例则保存在对象池
        if(isset($compCfg['is_singleton']) && $compCfg['is_singleton']){
            self::$compSingletonObjPool[$accessKey] = $compObj;
        }

        return self::$compSingletonObjPool[$accessKey];
    }

    /**
     * 扩展的 call_user_func_array 方法，在 $callable 中可用如下规则
     *
     * @aliasname/classname::staticMethod       调用静态方法
     * @aliasname/classname->commonMethod       初始化一个实例运行
     *
     * @param $callable
     * @param $args
     */
    public static function callUserFunction($callable, $args=array()){
        $cb = $callable;
        if(is_array($cb)){
            $cb[0] = self::getNamespaceByRoute($cb[0]);
        }
        //扩展方式 的首字母为 @
        if(is_string($cb)){
            $isStatic = explode("::", $callable);
            $isObj = explode("->", $callable);
            if(count($isStatic)==2){
                //静态方式
                $cb = array(self::getNamespaceByRoute($isStatic[0]), $isStatic[1]);
            }elseif(count($isObj)==2){
                //实例方式
                $cb = array(self::comp($isStatic[0]), $isStatic[1]);
            }else{

            }
        }

        if(!is_callable($cb)){
            throw new CspException('Param is not callable, cb: '.json_encode($callable), 10000);
        }
        return call_user_func_array($cb, $args);
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
    public static function getPathByRoute($oRoute, $suffix=''){
        $oRoute = trim($oRoute);
        $oRoute = rtrim($oRoute, '/');

        $firstChar = substr($oRoute,0,1);
        $rs = explode('/', $oRoute);

        if($firstChar==='@'){
            $rs[0] = self::getPathByAlias($rs[0]);
            return join(DIRECTORY_SEPARATOR, $rs).$suffix;
        }else{
            if($firstChar=='/' || substr($oRoute,1,1)===':'){
                return $oRoute.$suffix;
            }else{
                return self::appCfg('app_base_path').'/'.$oRoute.$suffix;
            }
        }
    }

    /**
     * 通过对象路由表达式，获取命名空间前缀
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
        $oRoute = str_replace('/', '\\', $oRoute);
        $firstChar = $oRoute[0];
        if($firstChar==='@'){
            $rs = explode('\\', $oRoute);
            $rs[0] = self::getNamespaceByAlias($rs[0]);
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
     * @param $aliasName string 以 @开头的别名字符串
     * @return string
     */
    public static function getPathByAlias($aliasName){
        $aliasName = trim($aliasName);
        if( is_array(self::$aliasMap[$aliasName]) && isset(self::$aliasMap[$aliasName][0]) ){
            return self::$aliasMap[$aliasName][0];
        }else{
            throw new CspException('Error alias name '.$aliasName.' config ');
        }
    }

    /**
     * 获取别名 对应的 命名空间 前缀 不包含最后的 \
     * @param $aliasName string 以 @开头的别名字符串
     * @return mixed
     */
    public static function getNamespaceByAlias($aliasName){
        $aliasName = trim($aliasName);
        if( is_array(self::$aliasMap[$aliasName]) && isset(self::$aliasMap[$aliasName][1]) ){
            return self::$aliasMap[$aliasName][1];
        }else{
            throw new CspException('Error alias name '.$aliasName.' config ');
        }
    }

    /**
     * 加载一个文件
     * @param $oRoute   string 可以包含别名前缀
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
     * 运行时加载应用的配置文件
     * @param $cfgFirstKey
     * @return  array
     */
    public static function loadAppConfig($cfgFirstKey){
        if(isset(self::$appCfg[$cfgFirstKey])){
            return self::$appCfg[$cfgFirstKey];
        }
        $cfgPath = self::$appCfg['app_cfg_path'];

        $cfgFile = $cfgPath.'/'.$cfgFirstKey.'.cfg.php';

        $cfgEnvFile = $cfgPath.'/'.self::getEnv().'/'.$cfgFirstKey.'.cfg.php';

        if(!file_exists($cfgFile)){
            if(!file_exists($cfgEnvFile)){
                throw new CspException("Can not find cfg file for key {$cfgFirstKey} , file : ".$cfgFile.' And '.$cfgEnvFile);
            }else{
               $cfgFile = $cfgEnvFile;
            }
        }
        self::$appCfg[$cfgFirstKey] = require($cfgFile);
        return self::$appCfg[$cfgFirstKey];

    }

    //-------------------------------------------------------------------
    /**
     * @param $obj
     * @param null $options
     */
    public static function initObjectOptions($obj, $options=null){
        if(!empty($options) && is_array($options)){
            foreach ($options as $k=>$v){
                $obj->$k = $v;
            }
        }
        return $obj;
    }
    /**
     * 实例化一个对象
     * @param $oRoute mixed 可以是如下值的一个
     *          实际的类名字符串，如： App\controlers\index
     *          一个对象，当传递一个对象时，实际上只进行 $opts 的属性赋值
     * @param array $opts
     * @return object
     */
    public static function newClass($oRoute, $opts=array(), $isSingleton=true){
        static $objs = array();

        $staticsKey = null;
        if(is_string($oRoute)){
            $staticsKey = $oRoute;
            if(isset($objs[$staticsKey]) && $isSingleton){
                return $objs[$staticsKey];
            }
            //获取类名
            $realNamespace = self::getNamespaceByRoute($oRoute);

            if(!class_exists($realNamespace)){
                throw new CspException("Error class oRoute, can not find calss {$oRoute} => {$realNamespace} ");
            }

            if($isSingleton){
                $objs[$staticsKey] = self::initObjectOptions(new $realNamespace(), $opts);
                return $objs[$staticsKey];
            }else{
                return self::initObjectOptions(new $realNamespace(), $opts);
            }

        }else{
            if(is_object($oRoute)){
                $staticsKey = get_class($oRoute);
                if($staticsKey=='Closure'){
                    throw new CspException("Error class oRoute, can not be Closure");
                }else{
                    return self::initObjectOptions($oRoute, $opts);
                }
            }else{
                throw new CspException("Error class oRoute, unknow type");
            }
        }
    }
    //-------------------------------------------------------------------


    /**
     * 统一规范 rest 接口，ajax ,以及 jsonp 接口 数据格式
     *
     * @param mixed  $rst
     * @param int    $code
     * @param string $msg
     * @param string $tips
     *
     * @return string jsonString
     */
    public static function wrapJsonApiData($rst, $code=0, $msg='OK', $tips=''){
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
        if(self::isDebug()){
            $r['status']= array_merge($r['status'], array(
                'time'  =>sprintf("%.4f ms",self::getTimeUse()*1000),
                'req'   =>self::request()->getRequestUri(),
                'sip'   =>self::request()->getServerIp(),
                'cip'   =>self::request()->getClientIp()
            ));
        }
        return json_encode($r);
    }
    /**
     * 初始化一些核心类
     *init core objs
     */
    private static function initCoreObjs(){
        self::$coreContainer['request']  = new CspRequest();
        self::$coreContainer['router']   = new CspRouter();
        self::$coreContainer['response'] = new CspResponse();
        self::$coreContainer['log']      = new CspLog();
        self::$coreContainer['tpl']      = new CspTemplate();
        self::$coreContainer['validator']= new CspValidator();
        if(self::isCli()){
            self::$coreContainer['cliConsole'] = new CspCliConsole();
        }
    }


    //------------------------------------------------------------------------------
    /**
     *
     * @return Csphp
     */
    public static function app(){
        return self::$app;
    }
    /**
     * @return CspRequest
     */
    public static function request(){
        return self::$coreContainer['request'];
    }
    /**
     * @return CspResponse
     */
    public static function response(){
        return self::$coreContainer['response'];
    }
    /**
     * @return CspRouter
     */
    public static function router(){
        return self::$coreContainer['router'];
    }

    /**
     * @return \Csp\base\CspBaseControler
     */
    public static function controler(){
        return self::router()->getControler();
    }

    /**
     * @return CspCliConsole
     */
    public static function cliConsole(){
        return self::$coreContainer['cliConsole'];
    }

    /**
     * @return CspLog
     */
    public static function log(){
        return self::$coreContainer['log'];
    }

    /**
     * @return CspTemplate
     */
    public static function view(){
        return self::$coreContainer['tpl'];
    }

    /**
     * @return CspValidator
     */
    public static function validator(){
        return self::$coreContainer['validator'];
    }
    //------------------------------------------------------------------------------

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
        static $inputData  = array();


        $vr = trim($vr, ' /');

        if(isset($vCache[$vr])){
            return $vCache[$vr];
        }

        //检查变更路由 的合法性
        if ( !preg_match("#^([gpcrfsehv-])(?::(.+))?\$#sim",$vr,$m) ){
            throw new CspException("Can't parse var from vRoute: {$vr} ");
        }

        //提取变量的类型 即第一个字符
        $vType = strtoupper(substr($vr,0,1));
        //提取变量的路径，即第3个字符之后
        $vPath= substr($vr, 2);
        $vKes = explode('/', $vPath);
        //初始化所有输入
        if(empty($inputData)){
            $inputData = array(
                'C'=>&$_COOKIE, 'G'=>&$_GET,	'P'=>&$_POST,'R'=>&$_REQUEST,
                'F'=>&$_FILES,	'S'=>&$_SERVER,	'E'=>&$_ENV,
                '-'=>&self::$appCfg
            );
        }

        if ($vType==='R' && empty($inputData['R'])) {
            $inputData['R'] = array_merge($_COOKIE, $_GET, $_POST);
        }

        //还没有取过 头信息， 立即获取
        if($vType==='H' && !isset($inputData['H'])){
            $inputData['H'] = getallheaders();
        }

        //还没有取过 路由变量信息， 立即获取
        if($vType==='V' && !isset($inputData['V'])){
            $inputData['V'] = self::router()->getRouteVars();
        }

        //如果使用的是子配置文件 ，又还没有加载，则尝试加载
        if($vType==='-' && !isset($inputData['-'][$vKes[0]])){
            $subCfg = self::loadAppConfig($vKes[0]);
        }

        $v  = null;
        $noFound = false;
        while ( $fk = array_shift($vKes) ) {
            if($v==null){
                if( isset($inputData[$vType][$fk]) ){
                    $v = $inputData[$vType][$fk];
                }else{
                    $noFound = true;
                    $v = $def;
                    break;
                    //return $def;
                }
            }else{
                if(isset($v[$fk])){
                    $v = $v[$fk];
                }else{
                    $noFound = true;
                    $v = $def;
                    break;
                    //return $def;
                }
            }
        }

        //没有验证规则，直接缓存后返回
        if(empty($rule)){
            $vCache[$vr] = $v;
            return $v;
        }

        //验证检查 缓存 返回
        if( CspValidator::validate(($noFound ? null : $v), $rule) ){
            $vCache[$vr] = $v;
            return $v;
        }else{
            $voidInfo = CspValidator::getVoidInfo($vPath, $tips);
            if($errHandle){
                //todo 替换为 内部扩展的 call_user_function
                if(!is_callable($errHandle)){
                    throw new CspException('Param errHandle is not callable: '.json_encode($errHandle), 10000, CspException::PARAM_INPUT_EXCEPTION);
                }
                call_user_func($errHandle, $voidInfo);
            }else{

                throw new CspException($voidInfo['tips'], $voidInfo['code']*1, CspException::PARAM_INPUT_EXCEPTION);
            }
            return null;
        }
    }

    /**
     * todo ...
     * URL 构造器
     * @param string        $r
     * @param string|array  $paramArrOrStr
     * @param string        $anchor
     * @param string        $hostKey
     * @return string url
     */
    public static function url($r, $paramArrOrStr='', $anchor='', $hostKey='_default'){
        if(is_array($paramArrOrStr)){
            $paramArrOrStr = http_build_query($paramArrOrStr);
        }
        //fix $anchor
        if($anchor && $anchor[0]!=='#'){
            $anchor = '#'.$anchor;
        }


    }

    //----------------------------------------------------------------------------------
    /**
     * 判断当前是否运行在生产环境
     * @return bool
     */
    public static function isProdEnv(){
        return self::getEnv() === self::ENV_TYPE_PROD;
    }
    /**
     * 判断当前是否运行在测试环境
     * @return bool
     */
    public static function isTestEnv(){
        return self::getEnv() === self::ENV_TYPE_TEST;
    }
    /**
     * 判断当前是否运行在开发环境
     * @return bool
     */
    public static function isDevEnv(){
        return self::getEnv() === self::ENV_TYPE_DEV;
    }

    /**
     * 设置运行环境
     * @param $evn
     */
    public static function setEnv($envStr=self::ENV_TYPE_PROD){
        self::$runningEnv = $envStr;
    }
    /**
     * 获取当前的运行环境
     * @return string
     */
    public static function getEnv(){
        return self::$runningEnv;
    }
    //----------------------------------------------------------------------------------

    /**
     * 判断当前是否运行在 CLI 环境
     * @return bool
     */
    public static function isCli(){
        return strtolower(PHP_SAPI)==='cli';
    }

    /**
     * 当前是否调试 模式，非调试模式 不输出 info log
     * @return bool
     */
    public static function isDebug(){
        return self::$isDebug;
    }

    /**
     * 设置 debug 模式, 开启DEBUG时将记录所有日志，显示所有错误
     * @param bool $debug
     */
    public static function setDebug($debug=true){
        self::$isDebug = $debug;
        //todo...
        if($debug){

        }else{

        }
    }
    //----------------------------------------------------------------------------------

    /**
     * 四种日志记录: debug info warning error
     *
     * @param mixed  $msg     日志信息，可以是包含 {{keyname}} 的字符串，或者一个数组
     * @param null   $context 上下文字典信息，$msg 中的 {{keyname}} 会替换为 $context[keyname]
     * @param string $type    日志的分类标识
     */
    public static function logDebug($msg, $context = NULL, $type = '') {
        self::log()->logDebug($msg, $context,$type);
    }
    public static function logInfo($msg, $context = NULL, $type = ''){
        self::log()->logInfo($msg, $context,$type);
    }
    public static function logWarning($msg, $context = NULL, $type = ''){
        self::log()->logWarning($msg, $context,$type);
    }
    public static function logError($msg, $context = NULL, $type = ''){
        self::log()->logError($msg, $context,$type);
    }


    //-------------------------------------------------------------------------
    /**
     *
     * 获取程序从启动现当前所用的时间
     *
     * @return mixed
     */
    public static function getTimeUse($dotLen=8){
        return sprintf("%.".$dotLen."f",microtime(true) - self::$appStartTime);
    }

    /**
     *
     * 用于计算时间的 开始位置
     *
     * @param string $flagKey
     */
    public static function bmkStart($flagKey){
        self::$bankmarkData[$flagKey] = microtime(true);
    }

    /**
     * 用于计算时间的结束位置
     * @param string $flagKey
     * @param int    $dotLen
     *
     * @return string
     */
    public static function bmkEnd($flagKey, $dotLen=6){
        return sprintf("%.".$dotLen."f",microtime(true) - self::$bankmarkData[$flagKey]);
    }

    /**
     * 调试方法 打印堆栈
     * @param null $traceInfo
     */
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
     * @param mixed $data
     * @param bool  $isVarDump
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
     * 开启 xhprof
     */
    public static function xhprofStart($opts=[]){
        if(is_bool($opts) && $opts===false){
            return;
        }
        if($opts===true){
            $opts = [];
        }

        self::xhprofCheck();
        Csp\comp\xhprof\CspExtXhprof::enable($opts);
    }

    /**
     * 开始 xhprof
     */
    public static function xhprofEnable($opts=[]){
        if(is_bool($opts) && $opts===false){
            return;
        }
        if($opts===true){
            $opts = [];
        }
        self::xhprofCheck();
        Csp\comp\xhprof\CspExtXhprof::enable($opts);
    }

    /**
     * 结束 xhprof , 如果需要记录整个请求，xhprofEnd 可以不调用
     */
    public static function xhprofEnd($opts=[]){
        self::xhprofCheck();
        Csp\comp\xhprof\CspExtXhprof::end($opts);
    }
    private static function xhprofCheck(){
        if(!function_exists('xhprof_enable')){
            throw new CspException("Pls install xhprof extention : https://pecl.php.net/package/xhprof ");
        }
    }
    //-------------------------------------------------------------------------
    /**
     * get system cfg
     *
     * @param string $k
     * @param null   $def
     */
    public static function sysCfg($k, $def=null){
        $vKeys = explode('/', $k);
        switch(count($vKeys)){
            case 1:
                return isset(self::$sysCfg[$k]) ? self::$sysCfg[$k] : $def ;
            case 2:
                return isset(self::$sysCfg[$k][$vKeys[1]]) ? self::$sysCfg[$k][$vKeys[1]] : $def;
            case 3:
                return isset(self::$sysCfg[$k][$vKeys[1]][$vKeys[2]]) ? self::$sysCfg[$k][$vKeys[1]][$vKeys[2]] : $def;
            case 4:
                return isset(self::$sysCfg[$k][$vKeys[1]][$vKeys[2]][$vKeys[3]]) ? self::$sysCfg[$k][$vKeys[1]][$vKeys[2]][$vKeys[3]] : $def;

        }
        return null ;
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


    //-------------------------------------------------------------------------
    /**
     * 触发一个事件
     * @param string    $eventName  事件名
     * @param mixed     $data       事件相关的数据
     * @param object    $senderObj  事件发送者，对象
     */
    public static function fireEvent($eventName, $data=null, $senderObj=null){
        CspEvent::fire($eventName, $data, $senderObj);
    }

    /**
     * 监听某个事件
     * @param string    $eventName  事件名
     * @param callable  $eventListener func(CspEvent $event){}
     */
    public static function on($eventName, $eventListener){
        CspEvent::on($eventName, $eventListener);
    }

    /**
     * 监听某个事件,将在PHP退出前执行
     * @param string    $eventName  事件名
     * @param callable  $eventListener func(CspEvent $event){}
     */
    public static function delayOn($eventName, $eventListener){
        CspEvent::delayOn($eventName, $eventListener);
    }


    /**
     * alias for self::on
     * 监听某个事件
     * @param $eventName        string   需要监听的事件名
     * @param $eventListener    callable 事件处理器，func(CspEvent $event){}
     */
    public static function listen($eventName, $eventListener){
        CspEvent::on($eventName, $eventListener);
    }
    //-------------------------------------------------------------------------
    /**
     * 静态方法 的 调用魔术方法,  todo...
     * @param $name
     * @param $arguments
     */
    public static function __callStatic($name, $arguments) {

        throw new CspException("Unknow static method Csphp::".$name);
    }

    //-------------------------------------------------------------------------
    //for debug...
    public static function tmp(){

        echo '<pre>';
        //var_dump(self::appCfg('app_namespace'));exit;
        //var_dump(self::getNamespaceByRoute('@ctrl/api/index'));
        //var_dump(self::newClass('@ctrl/api/index',array('var'=>'yxh')));
        //var_dump(self::newClass('@ctrl/api/index',array('var'=>'yxh2')));
        //var_dump(self::newClass('@ctrl/api/index',array('var'=>'yxh2'), false));
        echo "\n\nTime use: ".self::getTimeUse();
        //print_r($_SERVER);
    }
}
