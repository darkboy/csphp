<?php
namespace Csp\core;
use \Csphp as Csphp;
use \Csp\core\CspException   as CspException;


class CspLog{
    const LOG_TYPE_DEBUG    = 'debug';
    const LOG_TYPE_INFO     = 'info';
    const LOG_TYPE_WARINING = 'warning';
    const LOG_TYPE_ERROR    = 'error';

    /**
     * @var array
     */
    public $logOptions = array(
        'log_key_separator'=>'#####',
        'log_base_path'    =>__DIR__,
        'log_stay_days'    =>7,
        'is_log_info'      =>true,
        'is_log_debug'     =>true,
        'is_log_warning'   =>true,
        'is_log_error'     =>true,
    );
    /**
     * 日志的基准目录
     * @var string
     */
    public $logBasePath = '';
    /**日志保留的天数，超过的自动删除
     * @var int
     */
    public $saveDay = 7;
    /**
     * @var array 缓存日志信息 在程序结束的时候写入文件
     */
    public static $logInfoCache = array();

    public function __construct(){

        foreach($this->logOptions as $k=>$v){
            $this->logOptions[$k] = Csphp::sysCfg($k);
        }
        //在程序结束的时候写LOG
        register_shutdown_function(array($this,'writeAllLogAfterShutdown'));
    }

    /**
     * @param $k
     */
    public function option($k){
        return $this->logOptions[$k];
    }
    /**
     * 写入一条日志
     * @param $category         日志的分类信息
     * @param null $logStrOrArr 日志信息，可以包含 {{keyname}}
     * @param null $context 上下文字典信息，$msg 中的 {{keyname}} 会替换为 $context[keyname]
     */
    public function logDebug($category, $logStrOrArr=null, $context=null){
        if(!$this->option('is_log_debug')){
            return false;
        }
        $this->log(self::LOG_TYPE_DEBUG, $category, $logStrOrArr, $context);
    }

    /**
     * 写入一条日志
     * @param $category         日志的分类信息
     * @param null $logStrOrArr 日志信息，可以包含 {{keyname}}
     * @param null $context 上下文字典信息，$msg 中的 {{keyname}} 会替换为 $context[keyname]
     */
    public function logInfo($category, $logStrOrArr=null, $context=null){
        if(!$this->option('is_log_info')){
            return false;
        }
        $this->log(self::LOG_TYPE_INFO, $category, $logStrOrArr, $context);
    }

    /**
     * 写入一条日志
     * @param $category         日志的分类信息
     * @param null $logStrOrArr 日志信息，可以包含 {{keyname}}
     * @param null $context 上下文字典信息，$msg 中的 {{keyname}} 会替换为 $context[keyname]
     */
    public function logWarning($category, $logStrOrArr=null, $context=null){
        if(!$this->option('is_log_warning')){
            return false;
        }
        $this->log(self::LOG_TYPE_WARINING, $category, $logStrOrArr, $context);
    }

    /**
     * 写入一条日志
     * @param $category         日志的分类信息
     * @param null $logStrOrArr 日志信息，可以包含 {{keyname}}
     * @param null $context 上下文字典信息，$msg 中的 {{keyname}} 会替换为 $context[keyname]
     */
    public function logError($category, $logStrOrArr=null, $context=null){
        if(!$this->option('is_log_error')){
            return false;
        }
        $this->log(self::LOG_TYPE_ERROR, $category, $logStrOrArr, $context);
    }

    /**
     * @param $logType
     * @param $category
     * @param null $logStrOrArr
     * @param null $context
     */
    private function log($logType, $category, $logStrOrArr=null, $context=null){

        if(!$this->chkCategoryName($category)){
            throw new CspException('Log category is invalid ['.$category.']');
        }
        $k = $logType.$this->option('log_key_separator').$category;
        if(isset(self::$logInfoCache[$k])){
            self::$logInfoCache[$k] .= "\n".self::wrapLogStr($logStrOrArr, $context);
        }else{
            self::$logInfoCache[$k] .= self::wrapLogStr($logStrOrArr, $context);
        }
    }

    /**
     * 为防止目录
     * @param $category
     * @return bool
     */
    public function chkCategoryName($category){
        return strpos($category, '/')===false && strpos($category, '\\')===false;
    }

    /**
     * 创建日志目录
     */
    public function createLogPaths(){
        $path = rtrim($this->option('log_base_path'), '/\\');
        $path.='/'.date("Ymd").'';
        @mkdir($path, 0777, true);
        if(!file_exists($path)){
            throw new CspException('Can not create log path ['.$path.']');
        }
        return $path;
    }
    public function createLogFileName($type, $category){
        return $type.'.'.$category.'.log';
    }
    /**
     * 格式化日志信息
     * @param $logStrOrArr
     * @param null $context
     * @return mixed|string
     */
    public function wrapLogStr($logStrOrArr, $context=null){
        $ip = Csphp::request()->clientIp();
        $uri = Csphp::request()->reqRoute();
        $prefix = "[".date('H:i:s')."] ".$ip."\t".$uri."\t";
        if(is_array($logStrOrArr)){
            $logStr = json_encode($logStrOrArr);
            if(is_array($context)) {
                $logStr.="Context: ".json_encode($context);
            }
        }else{
            $logStr = $logStrOrArr;
            foreach($context as $k=>$v){
                $logStr = str_replace("{{".$k."}}", $v, $logStr);
            }
        }
        return $prefix.$logStr;
    }

    /**
     * 注册到 shutdown 方法中，在程序结束时写日志信息
     */
    public function writeAllLogAfterShutdown(){
        if(empty(self::$logInfoCache)) {
            return false;
        }
        $logPaths   = $this->createLogPaths();
        foreach(self::$logInfoCache as $k=>$logInfoText){
            list($type,$category) = explode($this->logOptions['log_key_separator'], $k, 2);
            $fileName = $this->createLogFileName($type, $category);
            $logFile  = $logPaths.'/'.$fileName;
            if (!@file_put_contents($logFile, $logInfoText, FILE_APPEND)){
                throw CspException('Can not write log file: '.$logFile." log info is ".htmlspecialchars($logInfoText));
            }
        }
        $this->cleanLogFiles();
    }

    /**
     * todo 清理日志
     */
    public function cleanLogFiles(){
        $stayDays = $this->option('log_stay_days');

    }

}
