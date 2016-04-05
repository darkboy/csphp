<?php
namespace Csp\base;

use \Csphp;
use \Csp\comp\CspCompDBMysqli;

class CspBaseModel {

    /**
     * 减少模型基类属性，将基类要用到的属性封装在一个总配置中
     * 通过 setOption getOption 访问，
     * 以减少模型基类对 模型使用对象模式时的干扰
     * @var array
     */
    protected $___modBaseAttr = array(
        //当前模型使用的 db 配置名 通过是 数据名
        'db'=>null,
        //当前模型对应的表名
        'tb'=>null,
        //主键 字段名
        'pk'=>null,
        //是否使用缓存 false true string
        'use_cache' =>false,
        //是否对返回的数据 做 k v 映射，通常情况下 使用 主键 做 k 对组织数据有帮助
        'use_map'   =>false,
        //当前模型: 'fieldName'=>array('typeOrFormat', 'create_validator','update_validator','default')
        'tb_fields'=>array(),
        //模型数据格式定义 fromaterName=>ruleStr
        'formaters'=>array(),
        //模型关系定义 relName=>array(relType, targetMod, targetFieldName, myField)
        'relations'=>array()
    );

    /**
     * @param string    $tbName
     * @param string    $pkName
     * @param bool      $useCache
     * @param string    $dbCfgName
     * @throws \Csp\core\CspException
     */
    public function initModel($tbName, $pkName='id', $useCache=false, $dbCfgName = null ) {
        $this->setOption('pk', $pkName);
        $this->setOption('tb', $tbName);
        $this->setOption('db', CspCompDBMysqli::getInstance($dbCfgName));
        $this->setOption('use_cache', $useCache);

        return $this;
    }


    /**
     * 如果未对数据表定义对应的 model 也可以直接 获取对应的模型操作
     * @param $tbName
     * @param $pkName
     * @param bool $useCache
     * @param string $dbCfgName
     * @return \Csp\base\CspBaseModel
     */
    public static function getModel($tbName, $pkName, $useCache=false, $dbCfgName = null){
        $mObj = new self();
        return $mObj->initModel($tbName, $pkName, $useCache, $dbCfgName);
    }


    /**
     * @param $k
     * @param $v
     */
    final public function setOption($k, $v){
        $this->___modBaseAttr[$k] = $v;
    }

    /**
     * @param $k
     * @return null
     */
    final public function getOption($k){
        return isset($this->___modBaseAttr[$k]) ? $this->___modBaseAttr[$k] : null;
    }

    /**
     * 获取一个DB实例
     * @return CspCompDBMysqli
     */
    final public function db($dbCfgName=null){
        return $dbCfgName==null ? $this->getOption('db') : CspCompDBMysqli::getInstance($dbCfgName);
    }
    /**
     * 初始化模型字段
     * @param array $fields
     */
    protected function initFields($fields=array()){
        return array(
            //'fieldName'=>array('type', 'create_validator','update_validator'),
        );

    }

    /**
     * 初始化模型数据格式
     * @param array $formater
     */
    protected function initFormaters($formater=array()){
        return array(
            'fkName'=>array(
                'fields'=>"*-a,b,c,d; id,text=json,ids=list,<relName:alias:num>",
                '-'=>"",
                "+"=>"<nodeName:>"
            ),
        );
    }


    /**
     * 定义 一对多 的模型关系
     * @param string $targetModRoute
     * @param string $targetField
     * @param string $myFieldForLink
     * @param string $relName
     */
    protected function relationHasMany($targetModRoute, $targetField='', $myFieldForLink='', $relName=''){

    }
    /**
     * 定义 一对一 的模型关系
     * @param string $targetModRoute
     * @param string $targetField
     * @param string $myFieldForLink
     * @param string $relName
     */
    protected function relationHasOne($targetModRoute, $targetField='', $myFieldForLink='', $relName=''){


    }

    protected function relationGet($relName){

    }

    protected function relationGets($relName, $pageSize=20, $page=1){

    }

    protected function checkModelData(){

    }

    protected function withMap($k=null){

    }





    public function toJson($data=null){}

    public function save($data=null){}
    public function insert($data=null){}
    public function create($data=null){}

    public function batchInsert($data){}
    public function batchCreate($data){}
    public function batchSave($data){}

    public function update($data=null){}
    public function delete($condOrPk){}
    public function pks($cond){}

    public function get($condOrPk){}
    public function gets($condOrPks){}
    public function formatGet($fk,$condOrPk){}
    public function formatGets($fk,$condOrPks){}


    public function find($cond,$page,$pageSize){}
    public function formatFind($fk,$cond,$page,$pageSize){}

    public function dataFormater($data, $isMulti=false){}


    public function __get() {

    }


    public function __set() {

    }

    public function __call(){}
}