<?php
namespace Csp\base;

use \Csphp;
use \Csp\comp\CspCompDBMysqli;

class CspBaseModel {

    /**
     * 减少模型属性，将基类要用到的属性封装在一个总配置中，访问，模型使用对象模式
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
        'use_cache'=>false,
        //当前模型: 'fieldName'=>array('typeOrFormat', 'create_validator','update_validator','default')
        'tb_fields'=>array(),
        //模型数据格式定义
        'formaters'=>array(),
        //模型关系定义
        'relations'=>array()
    );

    /**
     * @var CspCompDBMysqli
     */
    protected $__DB = null;
    protected $__TB = null;
    protected $__pk = null;
    /**
     * 模型字段描述
     * @var array
     */
    protected $__tableFields = array(
        //'fieldName'=>array('typeOrFormat', 'create_validator','update_validator'),
    );
    /**
     * 数据格式描述配置
     * @var array
     */
    protected $__formater = array();



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


    public function setOption($k, $v){
        $this->___modBaseAttr[$k] = $v;
    }
    public function getOption($k){
        return isset($this->___modBaseAttr[$k]) ? $this->___modBaseAttr[$k] : null;
    }

    /**
     * @return CspCompDBMysqli
     */
    public function db(){
        return $this->getOption('db');
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
                'fields'=>"*-a,b,c,d::id,text=json,ids=list,<nodeName as abc>",
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
    protected function initHasManyRelation($targetModRoute, $targetField='', $myFieldForLink='', $relName=''){

    }
    /**
     * 定义 一对一 的模型关系
     * @param string $targetModRoute
     * @param string $targetField
     * @param string $myFieldForLink
     * @param string $relName
     */
    protected function initHasOneRelation($targetModRoute, $targetField='', $myFieldForLink='', $relName=''){


    }

    protected function relationGet($relName){

    }

    protected function relationGets($relName, $pageSize=20, $page=1){

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