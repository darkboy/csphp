<?php
namespace Csp\ext\xhprof;

class CspExtXhprof{

    public static $runId = null;

    public function __construct() {
    }

    /**
     * 使用 xhprof 进行性能分析
     *
     * @param null $id
     */
    public static function enable(){
        /**
         * cpu: XHPROF_FLAGS_CPU
         * 内存: XHPROF_FLAGS_MEMORY
         *
         * 两个一起:
         * xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
         */

        self::$runId = rand(10000000,99999999);
        register_shutdown_function(array(new self(),'end'));
        xhprof_enable();
    }

    /**
     * @param null $flag
     */
    public static function start(){
        self::enable();
    }


    /**
     * 分析代码的结束处调用此方法
     * @return null|string
     */
    public static function end(){
        if(self::$runId){
            self::$runId = null;
        }else{
            return false;
        }
        // stop profiler
        $xhprof_data = xhprof_disable();

        // display raw xhprof data for the profiler run

        $XHPROF_ROOT = dirname(__FILE__).'/lib';
        include_once $XHPROF_ROOT . "/utils/xhprof_lib.php";
        include_once $XHPROF_ROOT . "/utils/xhprof_runs.php";

        // save raw data for this profiler run using default
        // implementation of iXHProfRuns.
        $xhprof_runs = new \XHProfRuns_Default();
        $save_id = isset($_GET['_xhprof']) ? $_GET['_xhprof'] : 'xhprof_tmp';
        // save the run under a namespace "xhprof_foo"
        $run_id = $xhprof_runs->save_run($xhprof_data, $save_id);
        return $run_id;
    }
}


