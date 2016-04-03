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
    public $viewBasePath    = '@m-view';

    /**
     * 当前控制器 action 对应的 模板 文件路径,
     * 示图模板的目录规范:
     *      一个 action       对应一个模板文件
     *      一个 controler    对应一个模板目录
     *      目录层次与 控制器层次相同
     * @var string
     */
    public $curViewFileForAction    = '';
    /**
     * 当前控制器对应的 模板目录
     * @var string
     */
    public $curViewPathForControler = '';
    /**
     * 当前公共模板目录，可放置 如 头 脚文件
     * @var string
     */
    public $curViewPathForInclude = '';
    public $tplFileExt = '.tpl.php';
    /**
     * xpipe 的标签占位符
     * @var string
     */
    public $pipeBoxTag  = '<div style="display: none;" xtype="xpipe-pos" id="{id}"><div>';
    public function __construct(){
    }

    /**
     * 获取当前 控制器 动作 对应的模板文件
     * @return string
     */
    public function getCurTplFileForAction(){
        if($this->curViewFileForAction){
            return $this->curViewFileForAction;
        }
        $path = $this->getCurTplPathForControler();
        $this->curViewFileForAction = $path.'/'.Csphp::router()->getActionName().$this->tplFileExt;
        return $this->curViewFileForAction;
    }

    /**
     * 获取当前控制器对应的模板目录
     * @return string
     */
    public function getCurTplPathForControler(){
        if($this->curViewPathForControler){
            return $this->curViewPathForControler;
        }
        $ctrlClsName = get_class(Csphp::controler());
        $ctrlNs = Csphp::getNamespaceByAlias('@ctrl');
        $tpl = substr($ctrlClsName, strlen($ctrlNs));
        $tpl = str_replace('\\', '/', trim($tpl,'\\/'));
        $this->curViewPathForControler = Csphp::getPathByRoute('@view/'.$tpl);
        return $this->curViewPathForControler;
    }

    /**
     * 获取当前模块的，共用模板目录
     */
    public function getCurViewIncludePath(){
        if($this->curViewPathForInclude){
            $this->curViewPathForInclude = Csphp::getPathByRoute('@m-view/common');
        }
        return $this->curViewPathForInclude;
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
     * @param $tplRoute string      渲染模板可用规则，见解释函数
     * @param $isReturn bool
     */
    public function render($___tplRoute='', $___data=array(), $___isReturn=true){

        //释放模板变量
        if(is_array($this->tplVars)){
            extract($this->tplVars, EXTR_SKIP);
        }
        if(is_array($___data)){
            extract($___data, EXTR_OVERWRITE);
        }
        //流入 控制器变量 到 模板，模板中可用 $this (模板类) $controler (当前控制器)
        $controler = Csphp::controler();

        if($___isReturn){
            ob_start();
            include $this->parseTplRoute($___tplRoute);
            return ob_get_clean();
        }else{
            include $this->parseTplRoute($___tplRoute);
        }

    }

    /**
     * @param array     $data
     * @param string    $tplRoute
     * @param bool      $isReturn
     */
    public function widget($tplRoute='', $data=array(), $isReturn=false){
        return $this->render($tplRoute, $data, $isReturn);
    }

    /**
     * 使用布局 进行渲染
     * @param string $layout
     * @param string $tplRoute
     * @param array $data
     * @param bool $isReturn
     */
    public function layout($layout,  $tplRoute='', $data=array(), $isReturn=false){
        return $this->render('@m-view/'.$layout,array( 'content'=>$this->render($tplRoute, $data, true)), $isReturn);
    }

    /**
     * 解释一个模板路由，返回示图文件地址，规则如下
     *        $tplRoute=""|null|-    - 号 或者 空值,则表示自动根据控制器 计算 view 路径
     *        $tplRoute=".pslName"   . 号开头，表示在当前控制器view目录中查找
     *        $tplRoute="@pslName"   @ 号开头，表示绝对路由，
     *        $tplRoute="pslName"    其它表示在当前模块的view目录中找
     * @param string $tplRoute
     */
    public function parseTplRoute($tplRoute){
        if(empty($tplRoute) || $tplRoute==='-'){
            return $this->getCurTplFileForAction();
        }
        if($tplRoute[0]==='.'){
            return $this->getCurTplPathForControler().'/'.substr($tplRoute, 1).$this->tplFileExt;
        }
        if($tplRoute[0]==='@'){
            return Csphp::getPathByRoute($tplRoute).$this->tplFileExt;
        }
        return Csphp::getPathByRoute('@m-view/'.ltrim($tplRoute, '/')).$this->tplFileExt;
    }


    /**
     * 支持三种 异步方式
     */
    public function async(){}

    /**
     * 将一个请求拆成N个请求
     */
    public function asyncByAjax(){}
    public function asyncByPipe(){}
    /**
     * 实现 PIPE 流输出 asyncByPipe 的别名
     */
    public function pls(){}


    /**
     *
     * @param $route
     */
    public function js($routeList, $opt=array()){

    }
    public function css($routeList, $opt=array()){

    }

    /**
     *
     */
    public function fn($route){
        //扩展名
        //$ext = $opt['ext'].'?V='.Csphp::appCfg('app_version', '0.0.'.date('Hids'));

    }

    /**
     * 输出运行时的数据给 前端
     * @param $route
     */
    public function data(){

    }



    // 用于模板中输出，转义HTML 除非你完全确定被输出的内容是安全 HTML 否则VIEW模板中的内容要求统一用这个接口输出
    /**
     * @param $str
     * @param int $quoteStyle
     * @return string
     */
    public function o ($str, $quoteStyle = ENT_COMPAT) {
        echo  htmlspecialchars($str, $quoteStyle);
        return '';
    }

    //
    /**
     * 用于模板中输出，第一个为选择条件
     * @param $if   bool 条件值
     * @param $yes  string 条件值为 true  时输出的值
     * @param $no   string 条件值为 false 时输出的值
     * @param $es   bool   是否要对输出进行转义
     */
    public function ifo($if,$yes,$no,$es=true){
        echo $if ? ($es ? $this->o($yes) : $yes) : ($es ? $this->o($no) : $no);
    }

}
