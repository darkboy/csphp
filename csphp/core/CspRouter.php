<?php
namespace Csp\core;
use Csp\base\CspBaseControler;
use Csphp;
use Closure;
/*
 * Csphp 中关于url 的概念约定
 *
 * http://www.csphp.com:80/path/to/setup/index.php/controler/action/v1-v1/v2-v2?a=1&b=2#abc
 *
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
        //入口文件
        'entry_file'    =>null,
        //URI 中 清除 变量路由 和 安装目录 后的路由
        'req_route'     =>null,

        //当前路由类型
        'route_type'    =>null,
        //匹配到的目标路由，可能是 闭包 数组 或者 包含变量的路由模板
        'target_route'  =>"",
        //解释 target_route 变量后的路由
        'parse_route'   =>"",

        //命中中的 路由规则名
        'hit_rule'      =>'',
        //匹配到的路由模板
        'match_key'     =>'',

        //解释结果，最终要执行的 路由信息
        'parse_rst'     =>array(
            'controler' =>null,
            'action'    =>null,
            'closure'   =>null
        ),

        //从路由规则中解释出来的 变量字典，可能是来自URL中的 /v1-v1/v2-v2 或者是 路由配置中的 "user/{actionVar}"
        'route_var'     => array(),
        //最后成功解释的控制器对象
        'controler'     => null
    );

    /*
     * Cli
     */
    public $cliInfo = array();


    /**
     * 控制器动作方法的 默认前缀
     * @var string
     */
    public $actionNamePrefix = 'action';
    /**
     * 控制器的 默认的 action 名称
     * @var string
     */
    public $actionNameIndex  = 'index';

    public function __construct(){
        $this->init();
    }

    /**
     * @return \Csp\core\CspRequest
     */
    public static function request(){
        return Csphp::request();
    }

    //初始化 路由
    public function init(){
        $this->routeInfo['uri']        = self::request()->getRequestUri();
        $this->routeInfo['setup_path'] = self::request()->getSetupPath();
        $this->routeInfo['entry_file'] = self::request()->getEntryFile();
        //初步解释URL上的路由变量，并提取 reqRoute
        $this->parseRequestUri();
        //echo '<pre>';print_r($this->routeInfo);
    }


    //-------------------------------------------------------------------------------------
    /**
     * 用户请求的路由，清除 路由变量,安装目录以及入口文件
     *
     */
    public function getRequestRoute(){
        return $this->routeInfo['req_route'];
    }

    /**
     * 获取 request_uri 用户请求的 原始URI，可能包含路由变量，querystring
     * @return string
     */
    public function getRequestUri(){
        return $this->routeInfo['uri'];
    }

    /**
     * 获取匹配的规则, {route_name}::{route_tpl}
     * @return string
     */
    public function getHitRule(){
        return $this->routeInfo['hit_rule'];
    }

    /**
     * 获取解释后的路由
     * @return string
     */
    public function getParseRoute(){
        return is_object($this->routeInfo['parse_route']) ? 'anonymous' : $this->routeInfo['parse_route'];
    }

    /**
     * 当前的 action 名称, 闭包 路由返回 -
     * @return string
     */
    public function getActionName(){

        $parseRst = $this->getParseRst();
        if(is_object($parseRst)){
            return '-';
        }
        if( is_array($parseRst) && isset($parseRst['action']) ){
            return $parseRst['action'];
        }
        return '';
    }

    /**
     * 获取路由解释结果 rst['controler'] rst['action']
     * @return array|callable| \Closure
     */
    public function getParseRst(){
        return $this->routeInfo['parse_rst'];
    }

    /**
     * 获取当前控制器
     * @return \Csp\base\CspBaseControler
     */
    public function getControler(){
        return $this->routeInfo['controler'];
    }

    /**
     * 获取全部或者一项路由信息
     * @param null|string $k 路由信息的key
     * @return array
     */
    public function getRouteInfo($k=null){
        return $k===null ? $this->routeInfo : (isset($this->routeInfo[$k]) ? $this->routeInfo[$k] : null);
    }

    /**
     * 获取路由变量
     * 获取路由解释过程中产生的变量
     * @return array
     */
    public function getRouteVars(){
        return $this->routeInfo['route_var'];
    }

    /**
     * 设置一个路由变量
     * @param string|array  $k
     * @param null|mixed    $v
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

    //-------------------------------------------------------------------------------------
    /**
     * 执行动作,产生相应的事件
     */
    public function doAction(){
        $routeRst = $this->getParseRst();
        //Csphp::dump($routeRst);exit;
        //准备执行 action
        Csphp::fireEvent(Csphp::EVENT_CORE_BEFORE_ACTION);
        $hasDoAction = false;
        do{
            //闭包路由
            $actionClosure = $routeRst['closure'];
            if($actionClosure instanceof Closure){
                $this->routeInfo['controler'] = $routeRst;
                $actionClosure($this->request());
                $hasDoAction = true;
                break;
            }

            //print_r($routeRst);
            //查找控制器失败,未分析到目标控制器
            if(!isset($routeRst['controler']) || !isset($routeRst['context']) || !$routeRst['context']['is_hit'] ){
                throw new CspException('404 Error route rst: '.json_encode($routeRst), 404, CspException::NOT_FOUND_EXCEPTION);
            }

            //Csphp::dump($routeRst);
            $actionName = $this->wrapActionName($routeRst['action']);
            $ctrlObj = $routeRst['context']['ctrl_obj'];
            $this->routeInfo['controler'] = $ctrlObj;

            //执行 filter , 是一个控制器预载逻辑，通常返回 acls 访问控制配置
            if(method_exists($ctrlObj, 'filter')){
                $acls = $ctrlObj->filter();
                if(!empty($acls)){
                    Csphp::checkAccessControl($acls);
                }
            }

            //执行 beforeAction
            if(method_exists($ctrlObj, 'beforeAction')){
                $ctrlObj->beforeAction();
            }

            //实际执行action
            $ctrlObj->$actionName($this->request());
            $hasDoAction = true;

            //执行 afterAction
            if(method_exists($ctrlObj, 'afterAction')){
                $ctrlObj->afterAction();
            }

            //error route rst
        }while(false);

        if($hasDoAction == false){
            throw new CspException('404 Error route , cant not find action to do route rst: '.json_encode($routeRst), 404, CspException::NOT_FOUND_EXCEPTION);
        }
        //执行 action 完成
        Csphp::fireEvent(Csphp::EVENT_CORE_AFTER_ACTION);
    }

    /**
     * 解释用户访问的原始路径
     *      提取路径中的路由变量
     *      删除路径中的入口文件、安装目录、querystring
     * @return string  requestRoute
     */
    public function parseRequestUri(){

        $urlPs      = explode('?', $this->getRoutePathInfo(), 2);
        $urlPath    = $urlPs[0];
        $urlPath    = trim($urlPath, '/\\');
        $urlPs      = explode('/', $urlPath);

        //ignore entryname
        $firstPart  = substr($urlPs[0],-4);
        if(strtolower($firstPart)==='.php'){
            array_shift($urlPs);
        }

        //echo '<pre>',$enterPrefix;print_r($urlPs);exit;
        $reqRoute   = '';
        $startRouterVar = false;
        foreach($urlPs as $p){
            if($startRouterVar || strpos($p, '-')){
                $vs = explode('-',$p,2);
                $this->setRouteVar($vs[0], isset($vs[1]) ? urldecode($vs[1]) : '');
                $startRouterVar = true;
            }else{
                $reqRoute.='/'.$p;
            }
        }
        $this->routeInfo['req_route'] = $reqRoute;
        return $reqRoute;
    }

    /**
     *
     * 获取与路由相关的 pathinfo ，默认为 uri ，也可以通过$_GET[r] 传递
     *
     * @return string route pathinfo
     */
    public function getRoutePathInfo(){
        if(isset($_GET['r'])){
            return $_GET['r'];
        }else{
            return $this->getRequestUri();
        }
    }


    /**
     * 解释当前请求 路由
     */
    public function parseRoute(){
        $findRst = $this->findRoute();
        foreach($findRst as $k=>$v){
            if($k==='route_var'){
                $this->setRouteVar($v);
            }else{
                $this->routeInfo[$k] = $v;
            }
        }
        //路由解释完成事件
        Csphp::fireEvent(Csphp::EVENT_CORE_AFTER_ROUTE);
        return true;
    }

    /**
     * 从 路由配置 中查找 条件匹配的 规则
     * array(
     * 'route_var'=>$matchVars,
     * 'match_key'=>$reqRoute,
     * 'target_source' =>$targetSource,
     * 'target_route'  =>$targetRoute
     * );
     */
    public function findRoute(){
        //当前请求路由
        $sourceReqRoute = $this->getRequestRoute();

        //----------------------------------------------------------------
        //访问首页，默认控制器,直接取当前模块的配置首页配置
        if(in_array($sourceReqRoute, array('/', '', '/index.php') ) ){
            $defaultRoute = Csphp::getModuleDefaultRoute();
            return array(
                'route_type'    => 'default',
                'route_var'     => [],
                'hit_rule'      => 'module_default_index',
                'match_key'     => '',
                'target_route'  => $defaultRoute,
                'parse_route'   => $defaultRoute,
                'parse_rst'     => $this->parseTargetRoute($defaultRoute),
            );
        }

        //----------------------------------------------------------------
        //检查是否真实路由
        $isRealRoute = $this->checkIsRealRoute($sourceReqRoute);
        //echo $sourceReqRoute;Csphp::dump($isRealRoute);exit;
        if($isRealRoute && is_array($isRealRoute)){
            return $isRealRoute;
        }

        //scan router config and find match rule
        foreach(Csphp::appCfg('router',array()) as $routeName=>$rCfg){
            if( !isset($rCfg['filter']) || $filterRst = Csphp::request()->isMatch($rCfg['filter']) ){

                //对请求路由进行预处理，如删除 静态化 的后缀 .html 等
                $reqRoute = $this->doRouteBeforeAction($sourceReqRoute, $rCfg);

                $findRst  = $this->findMatchRuleByRouteCfg($reqRoute, $rCfg);

                //echo '<pre>';print_r($findRst);//exit;
                if(empty($findRst)){continue;}

                //匹配成功
                if(!empty($findRst) && $findRst['route_type']!='real' ){
                    $findRst['hit_rule'] = $routeName."::".$findRst['match_key'];
                }
                //echo "<pre>Route find: \n";print_r($findRst);//exit;

                //有需求对目标路由进行后处理么 todo...
                //$realRoute = $this->doRouteAfterAction($reqRoute, $rCfg);

                return $findRst;
            }
            //echo "Filter rst: ".var_dump($filterRst);
        }
        return array();
    }

    /**
     * 对 req route 进行预处理，通常是删除后缀
     * @param string    $reqRoute
     * @param array     $rCfg
     * @return string   $reqRoute
     */
    private function doRouteBeforeAction($reqRoute,$rCfg){
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
    private function doRouteAfterAction($reqRoute,$rCfg){
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
    private function doActionForReqRoute($reqRoute, $actionCfg){
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
     * 过滤器通过后 从路由配置规则 rule_list 中查找 匹配的规则条件
     * 查找逻辑为:
     * 1. 检查是否有别名，有则，立即返回
     * 2. 检查当前的 请求 路由是否 存在 控制器文件，存在则返回
     * 3. 逐一 将 配置 规则 翻译 为正则表达式，并进行匹配，成功立即返回
     *
     * 可能的配置规则为:
     * 1. 别名 ，如 /req_route/ctrl1/action=>/req_route/ctrl2/action2
     * 2. 配置规则中 可能 包含变量 ，所有变量都可以在 目标路由中使用
     *      变量描述 {vname-type-len}
     *      vname       表示变量名，可以在目标路由中引用 如 "/api/user/{var_name}"
     *
     *
     *      type        表示匹配的字符类型,只有3种类型, 默认值为 s
     *              *   为任意字符
     *              d   数字
     *              s   为除 / 外的字符
     *
     *      len         表示匹配的字符的个数,默认值为 +
     *              *   为任意个字符,可以没有
     *              +   1个以上，不能没有
     *              1,3 1-3个
     *
     *
     *      {name}          表示 1位以上长度的字符，不包括 / 符
     *      {name-s}        表示 1位以上长度的字符，不包括 / 符,同上
     *      {name-*}        表示 1位以上长度的字符，包括 / 符
     *      {name-d}        表示 1位以上长度的数字
     *      {name-d-2}      表示 2位数字
     *      {name-d-2,4}    表示 2-4 位数字
     *      {name-s-+}      表示 1位以上的字符，
     *      {name-d-2,}     表示 2位以上的数字
     *
     * @param $reqRoute string  请求路由
     * @param $rCfg     array   配置列表 可能的配置规则如上:
     * @return $routeMatch array
     *  array(
     *  //路由成功匹配的类型: real alias regexp match
     *  'route_type'    =>'real',
     *  //根据 路由模板 和 当前请求 进行匹配后产生的路由变量
     *  'route_var'     =>array(),
     *  //匹配到的路由模板规则KEY
     *  'match_key'     =>'',
     *  //匹配到的目标路由，可能包含变量引用
     *  'target_route'  =>'',
     *  //解释后的目标路由，已解释目录路由中引用的变量
     *  'parse_route'   =>'',
     *  //真实的路由，将被执行
     *  'real_route'    =>array('controler'=>'','action'=>'')
     * )
     */
    public function findMatchRuleByRouteCfg($reqRoute, $rCfg){

        //匹配到的路由变量
        $matchVars  = array();

        //检查是否存在别名
        if(isset($rCfg['rule_list'][$reqRoute])){
            return array(
                'route_type'    => 'alias',
                'route_var'     => array(),
                'match_key'     => $reqRoute,
                'target_route'  => $rCfg['rule_list'][$reqRoute],
                'parse_route'   => $rCfg['rule_list'][$reqRoute],
                'parse_rst'     => $this->parseTargetRoute($rCfg['rule_list'][$reqRoute])
            );
        }

        //检查是否存在 非规则的控制器, 存在 则直接返回
        $isRealRoute = $this->checkIsRealRoute($reqRoute);
        if(!$isRealRoute && is_array($isRealRoute)){
            return $isRealRoute;
        }

        //echo '<pre>';
        //扫苗规则列表
        foreach($rCfg['rule_list'] as $rTpl=>$targetSourceRoute){
            //echo "\n\n",$rTpl," => ",$targetRoute;
            //检查是否正则表达路由,规则是正则达式时，必须以 # 作为分隔符
            if($rTpl[0]==='#'){
                $ruleRegexp = $rTpl;
                $matchRst = $this->checkReqRouteByRegexpRule($ruleRegexp, $reqRoute, $targetSourceRoute, $rTpl);
                if(empty($matchRst)){
                    continue;
                }else{
                    return $matchRst;
                }
            }

            //如果模板中不包含 { } 字符的 匹配模板，不包含变量
            $isVarTpl = strpos($rTpl,'{');
            if($isVarTpl){
                //前缀不同，不需要再进行正则匹配,直接进行下一条规则检查
                if(substr($rTpl,0, $isVarTpl)!== substr($reqRoute,0, $isVarTpl)){
                    continue;
                }else{
                    $ruleRegexp = self::compileRouteRuleToRegexp($rTpl);
                    $matchRst = $this->checkReqRouteByRegexpRule($ruleRegexp, $reqRoute, $targetSourceRoute, $rTpl);
                    if(empty($matchRst)){
                        continue;
                    }else{
                        return $matchRst;
                    }
                    //echo "\n\n".$reqRoute." ".$ruleRegexp."\n\n";
                }
            }else{
                //glob模式的，路由配置
                if(fnmatch($rTpl, $reqRoute)){
                    return array(
                        'route_type'    =>'match',
                        'route_var'     =>$matchVars,
                        'match_key'     =>$rTpl,
                        'target_route'  =>$targetSourceRoute,
                        'parse_route'   =>$targetSourceRoute,
                        'parse_rst'     =>$this->parseTargetRoute($targetSourceRoute)
                    );
                }else{
                    continue;
                }

            }

        }
        return array();
    }

    /**
     * 检查当前路由是否匹配规则模板，如果是，刚解释目标模板 如果有引用变量
     *
     * @param string $ruleRegexp        通过规则模板编译的正则表达式
     * @param string $reqRoute          当前请求路由
     * @param string $targetSourceRoute 目标路由模板，可能包括变量引用
     * @param string $matchKey          当前规则KEY
     * @return array                    返回匹配结果
     */
    private function checkReqRouteByRegexpRule($ruleRegexp, $reqRoute, $targetSourceRoute, $matchKey){
        //匹配不成功，进行下一条规则匹配
        if(!preg_match($ruleRegexp, $reqRoute, $m)){
            return false;
        }
        //只有目标路由是字符串 才需要解释 变量
        $needParse = is_string($targetSourceRoute) && strpos($targetSourceRoute,'{') ? true : false;
        $parseRoute = $targetSourceRoute;

        $matchVars= array();
        foreach($m as $k=>$v){
            if(is_numeric($k)){continue;}
            if($needParse){
                $parseRoute = str_replace("{".$k."}", $v, $parseRoute);
            }
            $matchVars[$k]=$v;
        }
        return array(
            'route_type'    =>'regexp',
            'route_var'     =>$matchVars,
            'match_key'     =>$matchKey,
            'target_route'  =>$targetSourceRoute,
            'parse_route'   =>$parseRoute,
            'parse_rst'     =>$this->parseTargetRoute($parseRoute)
        );
    }


    /**
     * 将路由规则配置，编译成 正则表达式，进行匹配
     * @param $ruleStr
     */
    private static function compileRouteRuleToRegexp($ruleStr){
        //配置规则为 {vname-type-len} 每一项的
        $defOpts = array(
            0=>'id',
            1=>'s',
            2=>'+',
            3=>null
        );

        $charsCfg = array(
            's'=>'[^/]',
            '*'=>'.',
            'd'=>'\\d',
        );

        $lenCfg = array(
            '*'=>'*',
            '+'=>'+'
        );

        $varPattern = "#".preg_quote('{')."([^\\{\\}]+)".preg_quote('}')."#sim";
        //echo $varPattern."\n";

        $ruleStr = preg_replace_callback($varPattern, function($m) use ($defOpts, $charsCfg, $lenCfg) {

            //检查分支语法 {vname-(sw1|sw2|sw3)} 或者 {vname-(?!sw1|sw2|sw3)}
            if(strpos($m[1], '-(') && substr($m[1],-1)===')'){
                $vs   = explode('-', trim($m[1]), 2);
                $name = $vs[0];
                $subExp = $vs[1];
                $regexp = '(?<'.$name.'>'.$subExp.(substr($subExp,1,2)=='?!' ? '[^/]+' : '').')';
                //echo $regexp;
                return $regexp;
            }

            //常规变量规则分解： {vname-type-len}
            $vs   = explode('-', trim($m[1]));
            $name = $vs[0];
            $type = isset($vs[1]) ? strtolower($vs[1]) : 's';
            $len  = isset($vs[2]) ? $vs[2] : '+';

            $regexp = '(?<'.$name.'>'.$charsCfg[$type].(isset($lenCfg[$len]) ? $len : '{'.$len.'}').')';
            return $regexp;
        },$ruleStr);
        return "#^".$ruleStr."\$#sim";

    }

    /**
     * 解释目标路由 返回 标准结构
     *
     * @param string $targetRoute 目标路由 只能是 数组，字符串  和 闭包对象
     *
     * @return array 返回一个标准的路由结果结构 ['type']
     */
    private function parseTargetRoute($targetRoute) {
        //---------------------------------------------------------------
        //目标路由是一个闭包
        if ($targetRoute instanceof Closure){
            return array(
                'type'      => 'closure',
                'controler' => null,
                'action'    => null,
                'closure'   => $targetRoute,
                'context'   => null
            );
        }
        //---------------------------------------------------------------
        //检查字符串目标路由
        return $this->checkIsControlerExists($targetRoute);
    }

    /**
     * 检查是否存在路由对应的控制器类 与 action
     */
    private function checkIsControlerExists($targetRoute){
        static $checkCache = [];

        //修正目标路径，非绝对路由 刚默认为本模块路由
        if ($targetRoute[0]!=='@' && $targetRoute[0]!=='\\'){
            $targetRoute = '@m-ctrl/' . ltrim($targetRoute,'/\\');
        }
        $targetRoute = rtrim($targetRoute, '/');

        //action 的类型 可能有三种： 闭包 类名::运作名 目标路径
        $actionType  = 'uri';
        if(strpos($targetRoute, '::')){
            $actionType = 'class';
            $targetRoute = str_replace('::', '/', $targetRoute);
        }

        //------------------
        //在扫苗路由时 大多数情况下 都不是真实路由，所以缓存 失败结果
        if(isset($checkCache[$targetRoute])){
            return $checkCache[$targetRoute];
        }
        //------------------

        $paths = explode('/', $targetRoute);
        if(count($paths)<2){
            throw new CspException("Error target route {$targetRoute}", 404, CspException::NOT_FOUND_EXCEPTION);
        }
        //print_r($paths);
        $noAction = false;
        $isHitRealControler = true;

        $action     = array_pop($paths);
        $classRoute = join('/', $paths);
        $ctrlFile   = Csphp::getPathByRoute($classRoute, '.php');
        //echo $ctrlFile;

        //控制器检测结果 上下文信息
        $context = [
            'is_hit'        => false,
            'ctrl_file'     => $ctrlFile,
            'file_exists'   => file_exists($ctrlFile),
            'class_exists'  => false,
            'action_exists' => false,
            'ctrl_obj'      => null
        ];
        do{
            if(!$context['file_exists']){
                break;
            }

            $controlerClass = Csphp::getNamespaceByRoute($classRoute);
            //var_dump($controlerClass,$classRoute);exit;
            $context['class_exists'] = class_exists($controlerClass);
            if(!$context['class_exists']){
                break;
            }

            $ctrlObj = new $controlerClass;
            $context['action_exists'] = method_exists($ctrlObj, $this->wrapActionName($action));
            if (!$context['action_exists']) {
                break;
            }

            $context['is_hit']  = true;
            $context['ctrl_obj']= $ctrlObj;
        }while(false);


        $checkCache[$targetRoute] = array(
            'type'      => $actionType,
            'controler' => $targetRoute,
            'action'    => $action,
            'closure'   => null,
            'context'   => $context
        );
        return $checkCache[$targetRoute];
    }

    /**
     * 检查是否常规路由
     */
    private function checkIsRealRoute($targetRoute){
        $isRealRoute = $this->checkIsControlerExists($targetRoute);
        if(!empty($isRealRoute) && $isRealRoute['context']['is_hit']){
            return array(
                'route_type'    => 'real',
                'route_var'     => array(),
                'match_key'     => '',
                'target_route'  => $targetRoute,
                'parse_route'   => $targetRoute,
                'parse_rst'     => $isRealRoute
            );
        }
        return false;
    }


    /**
     *
     * @param $name
     *
     * @return string
     */
    public function wrapActionName($name){
        return strpos($name, $this->actionNamePrefix)===0 ? $name : $this->actionNamePrefix.ucfirst($name);
    }

    /**
     * 用户自定义路由, 主要为组件提供动态更改路由的方式，
     *
     * 如 CspRouter::onRequest('/docs/{act_cmd}',function(){},array('host'=>'api.csphp.com'));
     *
     * @param $matchRule string
     * @param $callback callable 执行逻辑
     * @param $filter    array
     * @return void
     */
    public static function onRequest($matchRule, $callback, $filter=array()){
        array_unshift(Csphp::$appCfg['router'],array(
            'filter'=>$filter,
            'rule_list'=>array(
                $matchRule => $callback
            )
        ));
    }

    public function dump(){
        echo "<pre>\n";print_r($this->routeInfo);
    }
}
