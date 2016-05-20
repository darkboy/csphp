<?php
// stop profiler
$xhprof_data = xhprof_disable();

// display raw xhprof data for the profiler run
//print_r($xhprof_data);

$XHPROF_ROOT = realpath(dirname(__FILE__));
include_once $XHPROF_ROOT . "/utils/xhprof_lib.php";
include_once $XHPROF_ROOT . "/utils/xhprof_runs.php";

// save raw data for this profiler run using default
// implementation of iXHProfRuns.
$xhprof_runs = new XHProfRuns_Default();
$save_id = isset($_GET['_xhprof']) ? $_GET['_xhprof'] : 'xhprof_tmp';
// save the run under a namespace "xhprof_foo"
$run_id = $xhprof_runs->save_run($xhprof_data, $save_id);
