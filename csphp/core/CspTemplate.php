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
     * @var array
     */
    public $viewPathCfg     =array(
        '-'=>'',
        '.'=>''
    );
    /**
     * 当前控制器 action 对应的 模板,
     * 示图模板的目录规范:
     *      一个 action       对应一个模板文件
     *      一个 controler    对应一个模板目录
     *      目录层次与 控制器层次相同
     * @var string
     */
    public $viewForCurAction = '';
    /**
     * xpipe 的标签占位符
     * @var string
     */
    public $pipeBoxTag  = '<div style="display: none;" xtype="xpipe-pos" id="{id}"><div>';
    public function __construct(){
    }


    /**
     * 初始化 模板类
     * @param $ctrlObj
     */
    public function initByControler($ctrlObj){
        $this->tplVars['controler'] = $ctrlObj;
        $ctrlClsName    = get_class($ctrlObj);
        $actionName     = Csphp::router()->getActionName();
        $this->viewForCurAction = $actionName;
        echo 'ctrlClsName:'.$ctrlClsName;
    }
    /**
     * 给模板传递一个变量
     * @param string|array  $k
     * @param null          $v
     */
    public function assign($k,$v=null){
        if(is_array($k)){
            foreach($k as $kk=>$vv){
                $this->tplVars[$kk] = $vv;
            }
        }else{
            $this->tplVars[$k] = $v;
        }
    }


    /**
     * 渲染一个模板
     * @param $data     array       传递到模块中的变量
     * @param $tplRoute string      渲染模板可用如下规则
     *        $tplRoute=""|null     空值,则表示自动根据控制器 计算 view
     *        $tplRoute=".pslName"   . 号开头，表示在当前控制器view目录中查找
     *        $tplRoute="-pslName"   - 号开头，表示在当前模块的view基准目录的incluede中找
     *        $tplRoute="@pslName"   @ 号开头，表示绝对路由，
     *        $tplRoute="pslName"    数字 字母开头，表示在当前模块的view目录中找
     * @param $isReturn bool
     */
    public function render($data=array(), $tplRoute='', $isReturn=true){

        if(is_array($this->tplVars)){
            extract($this->tplVars, EXTR_SKIP);
        }
        $tplTargetFile = $this->parseTplRoute($tplRoute);
        ob_start();

        require $tplTargetFile;
        $data = ob_get_clean();
    }
    public function parseTplRoute($tplRoute){
        if(empty($tplRoute)){
            $tplTargetFile = $this->viewForCurAction;
        }
    }

    /**
     * 实现 PIPE 与 AJAX 异步 兼容
     */
    public function pls(){}
    public function widget(){}
    public function plugin(){}

    // 用于模板中输出，转义HTML 除非你完全确定被输出的内容是安全 HTML 否则VIEW模板中的内容要求统一用这个接口输出
    public static function o ($str, $quoteStyle = ENT_COMPAT) {
        echo  htmlspecialchars($str, $quoteStyle);
        return '';
    }
    //用于模板中输出，第一个为选择条件
    public static function ifo($if,$yes,$no,$es=true){
        echo $if ? ($es ? self::o($yes) : $yes) : ($es ? self::o($no) : $no);
    }

}
