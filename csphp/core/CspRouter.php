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

    }

    /**
     * 获取路由解释过程中产生的变量
     */
    public function getRouteVars(){}


    /**
     * 解释路径
     */
    public function parseUrl(){}

    public function parseCli(){
        //$cliData = self::parseCliArgs();
    }

    /**
     *
     * 解释命令行参数 cli.php -a -bv -c="v1 and v2" --long1 --long2 longv2 --long3="v1 and v2" str1 "str2 and str3"
     *
     * 解释为：
     *
     * {
     *  "kv":
     *      {"a":true,"b":"v","c":"v1 and v2","long1":true,"long2":"longv2","long3":"v1 and v2"},
     *   "v":
     *      ["str1","str2 and str3"]
     * }
     *
     *
     * @return array('kv'=>array(),'v'=>array());
     */
    public static function parseCliArgs(){
        global $argv;

        $argData = array(
            //有名称的参数
            'kv'=>array(),
            //无名称的参数
            'v'=>array()
        );
        $c = count($argv);
        for ($i=1; $i<$c; $i++) {

            $v = $argv[$i];
            $isLongOpt = substr($v,0,2)==='--';

            //长参数处理
            if($isLongOpt){
                $vn = substr($v,2);
                //argFormat: --longarg="abc def"
                if(strpos($vn,'=')){
                    $vs = explode('=', $vn, 2);
                    $argData['kv'][$vs[0]]=$vs[1];
                }else{
                    //argFormat: --longarg argv
                    if(!isset($argv[$i+1]) || substr($argv[$i+1],0,1)==='-'){
                        $argData['kv'][$vn] = true;
                    }else{
                        $argData['kv'][$vn] = $argv[$i+1];
                        $i++;
                    }

                }
            }
            //短参数处理
            $isShort = substr($v,0,1)==='-';
            if(!$isLongOpt && $isShort){
                //argFormat: -fvalue
                if(strlen($v)>2){
                    if(strpos($v,'=')){
                        $argData['kv'][substr($v,1,1)]=substr($v,3);
                    }else{
                        $argData['kv'][substr($v,1,1)]=substr($v,2);
                    }

                }else{
                    if(!isset($argv[$i+1]) || substr($argv[$i+1],0,1)==='-'){
                        $argData['kv'][substr($v,1,1)] = true;
                    }else{
                        $argData['kv'][substr($v,1,1)] = $argv[$i+1];
                        $i++;
                    }
                }

            }
            if(!$isLongOpt && !$isShort){
                $argData['v'][] = $v;
            }

        }

        return $argData;
    }

    /**
     * 解释当前请求 路由
     */
    public function parseRoute(){
        if(Csphp::request()->isCli()){
            $this->parseCli();
        }else{
            $this->parseUrl();
            $this->findRoute();
        }
    }

    /**
     * 从 路由配置 中查找 条件匹配的 规则
     */
    public function findRoute(){}

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
     * 用户自定义路由
     * @param $filter
     * @param $callback
     */
    public static function on($filter, $callback){

    }
}
