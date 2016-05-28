<?php
namespace App\models;

use Csphp;
use Csp\base\CspBaseModel;


//示例模型
class modelDemo extends CspBaseModel{

    //数据源名称，应用使用多库时可指定，默认数据源是 mysql 中配置 is_default 为 true 的 数据库，
    protected static $dsnName   = null;
    //模型对应的表名，默认为 不包含命名空间的 类名
    protected static $tableName = '';
    //模型对应的表的主键，可选的，默认为字段配置的第一个字段 或者 id
    protected static $tablePk   = null;

    //模型对应的表的 字段配置，请将 主键字段放在第一个定义；可选的，不配置时 不进行默认值 与 数据验证
    // fieldName or cfgArr: [fieldName, fieldType{s|d|i|b}, default_value, create_validator, update_validator]
    protected static $defineFields   = [
        //如果没有定义主键，定义在第一个的被视为主键
        'uid',
        ['user_name',   's',    'norequire,email','norequire,email',''],
        ['email',       's',    'norequire,email','norequire,email',''],
        ['age',         'i',    'norequire,is:int','norequire,is:int',''],

    ];

    //使用自动跟踪时间截字段，在创建 和 更新时自动处理
    protected static $useTimestamp=[
        'create'=>'create_at',
        'update'=>'update_at',
    ];

    //软删除配置
    protected static $useSoftDelete=[
        'field'=>'delete_at',
        //'delete_state'=>'time()',
        //'revert_state'=>'0',
    ];

    //数据格式 配置
    protected $useFormater   = [
        'fkName1'=>"id,text=json,ids=list,<DynamicFields:subFk>"
    ];

    //是否使用缓存
    protected $useCache=[
        'cache_comp'  =>'CACHE',
        'prefix'     =>'',
        'cache_cfg'  =>[
            'method_name'=>[

            ]
        ],

    ];

    /**
     * 动态字段 dynamic_field_name=>cfg
     * @var array
     */
    protected $useDynamicFields=[
        //
        'dynamic_field_name'=>[
            'arg_field'     =>'uid',
            'fetch_func'    =>'model'
        ],
    ];





    public function __construct() {
        parent::__construct();
    }

    /**
     * 动态结点
     * @param $rowData
     * @param $argField
     */
    public function __field__userInfo($rowData,$argField){

    }





    public function userInfo(){

    }



}