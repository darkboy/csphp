<?php
namespace Csp\base;
use \Csphp;

class CspBaseModel {

    protected $__curDB = null;
    protected $__pk    = null;
    /**
     * 模型字段描述
     * @var array
     */
    protected $__tableFields = array(
        //'fieldName'=>array('format', 'create_validator','update_validator'),
    );
    /**
     * 数据格式描述
     * @var array
     */
    protected $__formater = array();


    public function __construct($tableName,$dbName,$pkName = 'id' ) {
        $this->db = Csphp::comp('mysql');
    }


    protected function initFields($fields=array()){

    }
    protected function initFormaters($formater=array()){

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