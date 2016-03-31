<?php
namespace Csp\core;
use \Csphp;
/*
 * Csphp cli 模式相关的管理控制
 *
 */
class CspCliConsole{

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
        self::parseCliArgs();

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
        $cliArgv=$_SERVER['argv'];

        //解释后的 cli 参数值字典
        $argData = array(
            //有名称的参数
            'kv'=>array(),
            //无名称的参数
            'v'=>array()
        );
        $c = count($cliArgv);
        for ($i=1; $i<$c; $i++) {

            $v = $cliArgv[$i];
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
                    if(!isset($cliArgv[$i+1]) || substr($cliArgv[$i+1],0,1)==='-'){
                        $argData['kv'][$vn] = true;
                    }else{
                        $argData['kv'][$vn] = $cliArgv[$i+1];
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
                    if(!isset($cliArgv[$i+1]) || substr($cliArgv[$i+1],0,1)==='-'){
                        $argData['kv'][substr($v,1,1)] = true;
                    }else{
                        $argData['kv'][substr($v,1,1)] = $cliArgv[$i+1];
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
     * 解释cli中的路由信息
     */
    public static function parseRoute(){

    }
    public static function cliHelp() {
        echo "\n", "  欢迎使用Csphp命令行模式，命令格式如下:", "\n";
        echo "\n\t", "cli.php <Routename> [-xvalue ...] [-x value ...] [--argname value ...]  [--argname=value ...]  [\"<C|P|G>:<Querystring>\" ...] ", "\n";
        echo "\n\t", "Routename:\t为控制器路由名称，如  clidemo/test";

        echo "\n\n\t", "指定运行环境:\t--env [dev|test|prod] ", "";
        echo "\n\n\t", "短参数的格式:\t-xvalue 或者 -x value  将产生参数 x=value 的效果，可以用 APP::cliArg('x') 获取 ", "";
        echo "\n\t", "长参数的格式:\t--arg=value 或者 --arg value  将产生参数 arg=value 的效果，可以用 APP::cliArg('arg') 获取 ", "\n";


        echo "\n\t", "另外，可用 \"<C|P|G>:<Querystring>\" 模似 POST GET COOKIE 数值，用于调试WEB接口 格式说明如下: ", "";
        echo "\n\t", "C: 后面的数据将被填入 \$_COOKIE\t \"c:a=1\" 将产生 \$_COOKIE[a]=1 的效果";
        echo "\n\t", "G: 后面的数据将被填入 \$_GET\t 效果同上";
        echo "\n\t", "p: 后面的数据将被填入 \$_POST\t 效果同上", "\n";

        echo "\n\t", "查看帮助： cli.php [-h|--help|?|/?]  ";
        echo "\n\t", "完整示例： cli.php --env dev clidemo/test \"g:getval=1\" -s -aavalue --param1=value1 --boolvalue --param2 value2  ";
        echo "\n\t", "完整示例： cli.php --env test clidemo/test \"g:getval=1\" \"p:postval=2\"  ";

        echo "\n\n";
    }


    /**
     * 交互式脚本入口
     */
    public static function interaction(){
        self::interactionHelp();
        $isExit= false;
        do{
            $cmdStr = self::read();
            if(in_array(strtolower($cmdStr), array('h','?','??','help'))){
                self::interactionHelp();
            }
            if(in_array(strtolower($cmdStr), array('exit','quit','bye'))){
                exit(0);
            }
            //do any func...



        }while(!$isExit);
    }
    //交互式脚本的帮助文档
    public static function interactionHelp(){}

    //守护程序入口
    public static function daemon(){}
    //守护程序帮助
    public static function daemonHelp(){}

    /**
     * 读取标准输入
     * @return string
     */
    public static function  read(){
        return trim(fgets(STDIN));
    }

    /**
     * 标准输出
     * @param $msg
     * @return int
     */
    public static function  write($msg){
        return fwrite(STDOUT, $msg);
    }


}
