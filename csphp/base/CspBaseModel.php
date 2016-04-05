<?php
namespace Csp\base;

use \Csphp;
use \Csp\comp\CspCompDBMysqli;

class CspBaseModel {

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
        //'fieldName'=>array('format', 'create_validator','update_validator'),
    );
    /**
     * 数据格式描述配置
     * @var array
     */
    protected $__formater = array();



    /**
     * @param $tbName
     * @param string $pkName
     * @param bool $useCache
     * @param string $dbCfgName
     * @throws \Csp\core\CspException
     */
    public function initModel($tbName, $pkName='id', $useCache=false, $dbCfgName = null ) {
        $this->__pk = $pkName;
        $this->__TB = $tbName;
        $this->__DB = CspCompDBMysqli::getInstance($dbCfgName);

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
     * 初始化模型字段
     * @param array $fields
     */
    protected function initFields($fields=array()){

    }

    /**
     * 初始化模型数据格式
     * @param array $formater
     */
    protected function initFormaters($formater=array()){

    }


    /**
     * 定义模型关系
     * @param array $formater
     */
    protected function initRelation($rel=array()){
        return array(
            'hasMany'=>array('targetModName','uid','uid', 'nodeName'),
        );
    }



    public function save($data=null){}
    public function insert($data=null){}
    public function create($data=null){}
    public function batchInsert($data=null){}
    public function batchCreate($data=null){}
    public function batchSave($data=null){}

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