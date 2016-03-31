<?php
namespace Csp\core;
use \Csphp;
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
     * array(
     * 'route_var'=>$matchVars,
     * 'match_key'=>$reqRoute,
     * 'target_source' =>$targetSource,
     * 'target_route'  =>$targetRoute
     * );
     */
    public function findRoute(){
        $sourceReqRoute = $this->getReqRoute();
        foreach(Csphp::appCfg('router',array()) as $routeName=>$rCfg){
            if( !isset($rCfg['filter']) || $filterRst = Csphp::request()->isMatch($rCfg['filter']) ){
                //对请求路由进行预处理，如删除 静态化 的后缀 .html 等
                $reqRoute = $this->doRouteBeforeAction($sourceReqRoute, $rCfg);
                $findRst  = $this->findMatchRuleByRouteCfg($reqRoute, $rCfg);

                //echo '<pre>';print_r($findRst);//exit;
                if(empty($findRst)){
                    continue;
                }

                //解释真实控制器
                if(!isset($findRst['real_route']) || empty($findRst['real_route'])){

                    $realRoute = $this->doRouteAfterAction($findRst['target_route'], $rCfg);
                    $isControlerExists = $this->isControlerExists($realRoute);
                    if(!empty($isControlerExists)){
                        $findRst['real_route'] = $isControlerExists;
                    }else{
                        //todo 404 not fond
                        $findRst['real_route'] = 404;
                    }
                }
                //echo '<pre>';print_r($findRst);//exit;
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
     * 2. 配置规则中 可能 包含变量 ，所有变量都可以在 目标路由中使用
     *      总规则为 {vname-type-len-def}
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
     *      {name}          表示 任意长度的字符，不包括 / 符
     *      {name-s}        表示 任意长度的字符，不包括 / 符,同上
     *      {name-*}        表示 任意长度的字符，包括 / 符
     *      {name-d}        表示 任意长度的数字
     *      {name-d-2}      表示 2位数字
     *      {name-d-2,4}    表示 2-4 位数字
     *      {name-s-+}      表示 1位以上的字符，
     *      {name-d-2,}     表示 2位以上的数字
     *
     * @param $reqRoute string  请求路由
     * @param $rCfg     array   配置列表 可能的配置规则如上:
     *
     */
    public function findMatchRuleByRouteCfg($reqRoute, $rCfg){

        //匹配到的规则模板 key
        $matchKey   = "";
        //匹配到的路由变量
        $matchVars  = array();
        //匹配到的目录路由表达式
        $routeSource = '';
        //目标路由结果
        $routeRst    = '';

        //检查是否存在别名
        if(isset($rCfg['rule_list'][$reqRoute])){
            return array(
                'route_var'=>$matchVars,
                'match_key'=>$reqRoute,
                'target_source' =>$rCfg['rule_list'][$reqRoute],
                'target_route'  =>$rCfg['rule_list'][$reqRoute]
            );
        }

        //检查是否存在 非规则的控制器, 存在 则直接返回
        $realRoute = $this->doRouteAfterAction($reqRoute, $rCfg);
        $isControlerExists = $this->isControlerExists($realRoute);
        if(!empty($isControlerExists)){
            //print_r($isControlerExists);
            return array(
                'route_var'     =>$matchVars,
                'match_key'     =>'',
                'target_source' =>'',
                'target_route'  =>'',
                'real_route'    =>$isControlerExists
            );
        }


        //echo '<pre>';
        //扫苗规则列表
        foreach($rCfg['rule_list'] as $rTpl=>$targetRoute){
            //echo "\n\n",$rTpl," => ",$targetRoute;
            //如果模板中不包含 { } 字符的 匹配模板，不包含变量
            $isVarTpl = strpos($rTpl,'{');
            if($isVarTpl){
                //前缀不同，不需要再进行正则 匹配
                if(substr($rTpl,0, $isVarTpl)!== substr($reqRoute,0, $isVarTpl)){
                    continue;
                }else{
                    $ruleRegexp = self::compileRouteRuleToRegexp($rTpl);
                    //echo "\n\n".$reqRoute." ".$ruleRegexp."\n\n";
                    if(preg_match($ruleRegexp, $reqRoute,$m)){
                        $matchKey = $rTpl;
                        $routeRst = $targetRoute;
                        $routeSource = $targetRoute;
                        foreach($m as $k=>$v){
                            if(!is_numeric($k)){
                                $routeRst = str_replace("{".$k."}", $v, $routeRst);
                                $matchVars[$k]=$v;
                            }
                        }
                    }

                }
            }else{

                if(fnmatch($rTpl, $reqRoute)){
                    $matchKey = $rTpl;
                    $routeRst = $targetRoute;
                    $routeSource = $targetRoute;
                }else{
                    continue;
                }

            }
            //如果成功匹配，则返回
            if($matchKey){
                return array(
                    'route_var'=>$matchVars,
                    'match_key'=>$matchKey,
                    'target_source' =>$routeSource,
                    'target_route'  =>$routeRst
                );
            }
        }
        return array();
    }


    /**
     * 将路由规则配置，编译成 正则表达式，进行匹配
     * @param $ruleStr
     */
    public static function compileRouteRuleToRegexp($ruleStr){
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
            //分解规则
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
     * 检查是否存在真实的控制器
     *
     * @param $realRoute
     * @return array
     */
    public function isControlerExists($realRoute){

        if(substr($realRoute,0,1)!=='@'){
            $realRoute = '@ctrl'.$realRoute;
        }
        $realRoute = rtrim($realRoute, '/');

        $paths = explode('/',$realRoute);
        $actoin = array_pop($paths);
        $realRouteShort = join('/', $paths);

        $ctrlFile = Csphp::getPathByRoute($realRouteShort, '.php');
        //echo $ctrlFile;
        if(file_exists($ctrlFile)){
            return array(
                'action'        =>$actoin,
                'ctontroler'    =>$realRouteShort
            );
        }
        $ctrlFile =  Csphp::getPathByRoute($realRoute, '.php');
        if(file_exists($ctrlFile)){
            return array(
                'action'        =>'index',
                'ctontroler'    =>$realRoute
            );
        }
        return array();
    }

    /**
     * 用户自定义路由
     * @param $filter
     * @param $callback
     */
    public static function on($filter, $callback){

    }
}
