<?php
namespace Csp\comp\xhprof;

class CspExtXhprof{

    public static $runId = null;
    //一些选项
    public static $options = [
        //直接指定 save id, 优先级最高
        'save_id'       => null,
        //指定 save_id 的变更名 ，save_id = $_GET[save_id_key]
        'save_id_key'   => '_xhprof',
        'save_id_def'   => 'xhprof_tmp'
    ];

    public function __construct() {
    }

    /**
     * 使用 xhprof 进行性能分析
     *
     * @param null $id
     */
    public static function enable($opts=[]){
        /**
         * cpu: XHPROF_FLAGS_CPU
         * 内存: XHPROF_FLAGS_MEMORY
         *
         * 两个一起:
         * xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
         */

        self::setOptions($opts);
        self::$runId = rand(10000000,99999999);
        register_shutdown_function(array(new self(),'end'));
        xhprof_enable();
    }

    /**
     * @param null $flag
     */
    public static function start($opts=[]){
        self::enable();
    }


    /**
     * 分析代码的结束处调用此方法
     * @return null|string
     */
    public static function end($opts=[]){
        if(self::$runId){
            self::$runId = null;
        }else{
            return false;
        }
        self::setOptions($opts);
        // stop profiler
        $xhprofData = xhprof_disable();

        // display raw xhprof data for the profiler run

        $XHPROF_ROOT = dirname(__FILE__).'/lib';
        include_once $XHPROF_ROOT . "/utils/xhprof_lib.php";
        include_once $XHPROF_ROOT . "/utils/xhprof_runs.php";

        // save raw data for this profiler run using default
        // implementation of iXHProfRuns.
        $xhprofObj = new \XHProfRuns_Default();

        $saveId = self::getSaveId();
        // save the run under a namespace "xhprof_foo"
        $run_id = $xhprofObj->save_run($xhprofData, $saveId);
        return $run_id;
    }

    private static function getSaveId(){
        if(self::$options['save_id']){
            return self::$options['save_id'];
        }
        $k = self::$options['save_id_key'];
        return isset($_GET[$k]) && $_GET[$k] ? $_GET[$k] :  self::$options['save_id_def'];
    }

    private static function setOptions($opts){
        foreach($opts as $k=>$v){
            self::$options[$k]=$v;
        }
    }
}


