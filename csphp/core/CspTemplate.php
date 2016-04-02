<?php
namespace Csp\core;
use \Csphp;

class CspTemplate{

    /**
     * 模板变量
     * @var array
     */
    public $tplVars     = array();
    /**
     *
     * @var string
     */
    public $viewBasePath    = '';
    /**
     * xpipe 的标签占位符
     * @var string
     */
    public $pipeBoxTag  = '<div style="display: none;" xtype="xpipe-pos" id="{id}"><div>';
    public function __construct(){

        Csphp::on(Csphp::EVENT_CORE_AFTER_COMP_INIT,function($event){

        });
    }



    public function assign(){}
    public function render(){
        $tplTargetFile = '';

        ob_start();
        require $tplTargetFile;
        $data = ob_get_clean();
    }

    public function pls(){}
    public function widget(){}
    public function plugin(){}

    public function o(){}
    public function ifo(){}

}
