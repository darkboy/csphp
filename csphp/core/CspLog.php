<?php
namespace Csp\core;

use \Csphp;
use \Csp\core\CspException;


class CspLog {
    const LOG_TYPE_DEBUG    = 'debug';
    const LOG_TYPE_INFO     = 'info';
    const LOG_TYPE_WARINING = 'warning';
    const LOG_TYPE_ERROR    = 'error';

    /**
     * @var array
     */
    public $logOptions = array(
        'log_key_separator' => ':#####:',
        'log_base_path'     => __DIR__,
        'log_stay_days'     => 7,
        'is_log_info'       => true,
        'is_log_debug'      => true,
        'is_log_warning'    => true,
        'is_log_error'      => true,
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

    public static $hasFlush = false;
    /**
     * @var array 缓存日志信息 在程序结束的时候写入文件
     */
    public static $logInfoCache = array();

    public function __construct() {

        foreach ($this->logOptions as $k => $v) {
            $this->logOptions[$k] = Csphp::sysCfg($k);
        }
        //在程序结束的时候写LOG
        register_shutdown_function(array($this, 'writeAllLogAfterShutdown'));
    }

    /**
     * @param $k
     */
    public function option($k) {
        return $this->logOptions[$k];
    }

    /**
     * 写入一条debug日志
     *
     * @param mixed  $logStrOrArr 日志信息，可以包含 {{keyname}}
     * @param null   $context     上下文字典信息，$msg 中的 {{keyname}} 会替换为 $context[keyname]
     * @param string $category    日志的分类标识
     */
    public function logDebug($logStrOrArr = NULL, $context = NULL, $category='') {
        if (!$this->option('is_log_debug')) {
            return false;
        }
        $this->log(self::LOG_TYPE_DEBUG, $category, $logStrOrArr, $context);
    }

    /**
     * 写入一条info日志
     *
     * @param mixed  $logStrOrArr 日志信息，可以包含 {{keyname}}
     * @param null   $context     上下文字典信息，$msg 中的 {{keyname}} 会替换为 $context[keyname]
     * @param string $category    日志的分类标识
     */
    public function logInfo($logStrOrArr = NULL, $context = NULL, $category='') {
        if (!$this->option('is_log_info')) {
            return false;
        }
        $this->log(self::LOG_TYPE_INFO, $category, $logStrOrArr, $context);
    }

    /**
     * 写入一条warning日志
     *
     * @param mixed  $logStrOrArr 日志信息，可以包含 {{keyname}}
     * @param null   $context     上下文字典信息，$msg 中的 {{keyname}} 会替换为 $context[keyname]
     * @param string $category    日志的分类标识
     */
    public function logWarning($logStrOrArr = NULL, $context = NULL, $category='') {
        if (!$this->option('is_log_warning')) {
            return false;
        }
        $this->log(self::LOG_TYPE_WARINING, $category, $logStrOrArr, $context);
    }

    /**
     * 写入一条Error日志
     *
     * @param mixed  $logStrOrArr 日志信息，可以包含 {{keyname}}
     * @param null   $context     上下文字典信息，$msg 中的 {{keyname}} 会替换为 $context[keyname]
     * @param string $category    日志的分类标识
     */
    public function logError($logStrOrArr = NULL, $context = NULL, $category='') {
        if (!$this->option('is_log_error')) {
            return false;
        }
        $this->log(self::LOG_TYPE_ERROR, $category, $logStrOrArr, $context);
    }

    /**
     * @param      $logType
     * @param      $category
     * @param null $logStrOrArr
     * @param null $context
     */
    private function log($logType, $category, $logStrOrArr = NULL, $context = NULL) {

        if (!$this->chkCategoryName($category)) {
            throw new CspException('Log category is invalid [' . $category . ']');
        }
        $k = $category ? $logType . $this->option('log_key_separator') . $category : $logType;

        if ( isset(self::$logInfoCache[$k]) ) {
            self::$logInfoCache[$k] .= "\n" . self::wrapLogStr($logStrOrArr, $context);
        } else {
            self::$logInfoCache[$k]  = self::wrapLogStr($logStrOrArr, $context);
        }

        if(self::$hasFlush){
            $this->flush();
        }
    }

    /**
     * 为防止目录越界，检查类型串 不能包括目录符
     *
     * @param $category string
     *
     * @return bool
     */
    private function chkCategoryName($category) {
        return strpos($category, '/') === false && strpos($category, '\\') === false;
    }

    /**
     * 创建日志目录
     */
    private function createLogPaths() {
        $path = rtrim($this->option('log_base_path'), '/\\');
        $path .= '/' . date("Ymd") . '';
        @mkdir($path, 0777, true);
        if (!file_exists($path)) {
            throw new CspException('Can not create log path [' . $path . ']');
        }

        return $path;
    }

    private function createLogFileName($type, $category) {
        if($category){
            $category = '.' . $category;
        }
        return $type . $category . '.log';
    }

    /**
     * 格式化日志信息
     *
     * @param      $logStrOrArr
     * @param null $context
     *
     * @return mixed|string
     */
    private function wrapLogStr($logStrOrArr, $context = NULL) {
        $ip = Csphp::request()->getClientIp();
        $uri = Csphp::request()->getReqRoute();
        $prefix = "[" . date('H:i:s') . "] " . $ip . "\t" . $uri . "\t";
        if (is_array($logStrOrArr)) {
            $logStr = "LogData:".json_encode($logStrOrArr);
            if (is_array($context)) {
                $logStr .= "\tlogContext: " . json_encode($context);
            }
        } else {
            $logStr = $logStrOrArr;

            if (is_array($context)) {
                foreach ($context as $k => $v) {
                    $logStr = str_replace("{{" . $k . "}}", $v, $logStr);
                }
                $logStr .= "\tlogContext: " . json_encode($context);
            }
        }

        return $prefix . $logStr;
    }

    /**
     * 注册到 shutdown 方法中，在程序结束时写日志信息
     */
    public function writeAllLogAfterShutdown() {
        //日志写入
        $this->flush();
        //日志清理 一个请求只做一次 且在请求完成后写入 不影响用户体验
        $this->cleanLogFiles();
    }


    private function flush(){
        //no andy log info to write
        if (empty(self::$logInfoCache)) {
            return false;
        }
        $logPaths = $this->createLogPaths();
        foreach (self::$logInfoCache as $k => $logInfoText) {
            list($type, $category) = array_pad(explode($this->logOptions['log_key_separator'], $k, 2),2,'');

            $fileName = $this->createLogFileName($type, $category);
            $logFile = $logPaths . '/' . $fileName;

            if(file_exists($logFile)){
                $logInfoText = "\n".$logInfoText;
            }
            if (!$this->logIOWrite($logFile, $logInfoText)) {
                throw new CspException('Can not write log file: ' . $logFile . " log info is " . htmlspecialchars($logInfoText));
            }
        }
        self::$hasFlush = true;
    }



    /**
     * 清理日志 只保留 log_stay_days 天的日志，日志是按天存放
     */
    private function cleanLogFiles() {
        $stayDays = $this->option('log_stay_days');
        $path = rtrim($this->option('log_base_path'), '/\\');
        $fs = glob($path."/*");

        $stayDays = 1;
        $expireTime = strtotime("-{$stayDays} day");
        $expireDate = date("Ymd", $expireTime);

        foreach($fs as $f){
            if(is_dir($f)){
                $logDate = substr($f,-8);
                if(+$logDate <= +$expireDate){
                    $this->rmDir($f);
                }
            }
        }
        //echo "<pre>\n",date("Y-m-d", $expireTime); print_r(glob($path."/*"));
    }

    /**
     * 递归删除一个目录以及它包括的文件
     * @param $dir
     *
     * @return bool
     */
    private function rmDir($dir){
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->rmDir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        return !!rmdir($dir);
    }


    /**
     * 日志的IO
     */
    private function logIOWrite($logFile, $logInfoText){
        return @file_put_contents($logFile, $logInfoText, FILE_APPEND);
    }
}
