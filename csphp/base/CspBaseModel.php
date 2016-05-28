<?php
namespace Csp\base;
use Csp\core\CspException;
use Csphp;
//use Csp\comp\db\;
use ArrayAccess;

class CspBaseModel extends CspBaseObject implements ArrayAccess {

    /**
     * 减少模型基类属性，将基类要用到的属性封装在一个总配置中
     * 通过 setOption getOption 访问，
     * 以减少模型基类对 模型使用对象模式时的干扰
     * @var array
     */
    protected $___modBaseAttr = array(
        //当前模型使用的 db 配置名 通常是 数据源名
        'db'           => NULL,
        //当前模型对应的表名
        'tb'           => NULL,
        //主键 字段名
        'pk'           => 'id',
        //是否使用缓存 false true string
        'use_cache'    => false,
        //缓存前缀
        'cache_prefix' => false,
        //是否对返回的数据 做 k v 映射，通常情况下 使用 主键 做 k 对组织数据有帮助
        'use_map'      => false,
        //当前模型: 'fieldName'=>array('typeOrFormat', 'create_validator','update_validator','default')
        'tb_fields'    => array(),
        //当前模型的数据
        'attr_data'    => array(),
        //模型数据格式定义 fromaterName=>ruleStr
        'formaters'    => array(),
        //模型关系定义 relName=>array(relType, targetMod, targetFieldName, myField)
        'relations'    => array(
            //一对一关系
            'o-o' => array(),
            //一对多
            'o-m' => array(),
            //多对多关系
            'm-m' => array(),
        )
    );

    //当前实例
    protected static $instance = null;

    /**
     * @param string    $tbName
     * @param string    $pkName
     * @param bool      $useCache
     * @param string    $dsnName
     *
     * @throws \Csp\core\CspException
     */
    public function initModel($tbName, $pkName='id', $useCache=false, $dsnName = null) {
        $this->setOption('pk', $pkName);
        $this->setOption('tb', $tbName);
        $this->setOption('db', Csphp::comp('DB')->getConnection($dsnName));
        $this->setOption('use_cache',       $useCache);
        $this->setOption('cache_prefix',    $useCache);

        return $this;
    }

    public function callByCache($cacheOpt){

    }
    public function callByXhporf($cacheOpt){

    }
    public function callByTime($cacheOpt){

    }
    public function callByTrace($cacheOpt){

    }


    /**
     * 如果未对数据表定义对应的 model 也可以直接 获取对应的模型操作
     * @param string    $tbName
     * @param string    $pkName
     * @param bool      $useCache
     * @param string    $dbCfgName
     * @return \Csp\base\CspBaseModel
     */
    public static function getModel($tbName, $pkName, $useCache=false, $dbCfgName = 'default'){
        $mObj = new static();
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
    //-------------------------------------------------------------------------------

    /**
     * 获取一个DB实例
     * @return CspCompDBMysqli
     */
    final public function db($dsnName=null){
        return $dsnName==null ? $this->getOption('db') : Csphp::comp('DB')->getConnection($dsnName);
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
     * 初始化模型数据格式,格式名称=>格式规则
     *
     *
     *      <formatName>=><formatRule>
     * @param array $formater
     */
    protected function initFormaters($formater=array()){
        return array(
            'fkName1'=>"id,text=json,ids=list,<hasOneRelName:subFk>",
            'fkName2'=>"*-a,b,c,d;",
            'fkName3'=>"*-a,b,c,d; id,text=json,ids=list,<hasManyRelName:subFk:alias:num>",
        );
    }


    /**
     * 定义 一对多 的模型关系
     *
     * userInfo ,   uid<-->user.uid
     * myFriends ,  uid <--> friendList.uid <--> friendList.fuid<-->uid
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
    //--------------------------------------------------------------------

    /**
     * 获取模型的一个实例
     *
     * @param bool $newInstance
     *
     * @return static
     */
    public static function getInstance($newInstance=false){
        if($newInstance){
            return new static();
        }
        if(self::$instance===null){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function reset(){

    }

    //-------------------------------------------------------------------------
    /**
     * @param null $keyField
     */
    public static function withMap($keyField=null){
        return self::getBuilderInstance();
    }

    public static function withXhprof($k=null){
        return self::getBuilderInstance();
    }

    public static function withCache($cacheOpt=null){

        return self::getBuilderInstance();
    }
    public static function withNoCache($cacheOpt=null){

        return self::getBuilderInstance();
    }

    public static function withFormat($formatName){

        return self::getBuilderInstance();
    }

    public static function withFields($fieldStrOrArr){

        return self::getBuilderInstance();
    }

    public static function withTime(){
        return self::getBuilderInstance();
    }

    public static function withTrace(){
        return self::getBuilderInstance();
    }

    public static function withCount($cntField){
        return self::getBuilderInstance();
    }

    public static function withQueryOption($opt){

        return self::getBuilderInstance();
    }

    /**
     * @return static
     */
    public static function getBuilderInstance(){
        return new static();
    }
    //-------------------------------------------------------------------------



    public static function toJson($data=null){}

    /**
     * 使用验证器，检查 模型数据, 如果 不传递参数，则验证当前模型
     *
     * @param null|array    $data
     * @param bool          $isMulti
     */
    public static function checkModelData($data=null, $isMulti=false){

    }

    /**
     * 如果参数为 0 个当成实例调用, 如果
     * @param null $data
     */
    public static function insert($data=null){}
    public static function create($data=null){}

    public static function batchInsert($data){}
    public static function batchCreate($data){}
    public static function batchSave($data){}

    public static function save($data=null){}
    public static function update($data=null){}

    public static function delete($condOrPk=null){}
    public static function pks($cond){}

    public static function get($condOrPk){}
    public static function gets($condOrPks){}

    public static function find($cond, $page, $pageSize){}
    public static function where($field=null, $value=null, $opt=null){}


    public static function formatGet($fk, $condOrPk){}
    public static function formatGets($fk, $pks){}
    public static function formatFind($fk, $cond, $page,$pageSize){}

    public function dataFormat($data, $isMulti=false){}


    /**
     *
     */
    public function __get() {

    }


    /**
     *
     */
    public function __set() {

    }

    /**
     *
     */
    public function __call(){}


    /**
     * __type__ 开头的成员方法是 用户自定义类型的系列化 和 反系列化方法
     *
     * __type__<typeName>_encode
     * __type__<typeName>_decode
     *
     * @param $v
     */
    final protected function __type__list_encode($v){}
    final protected function __type__list_decode($v){}

    /**
     * json 数据
     * @param $v
     */
    final protected function __type__json_encode($v){}
    final protected function __type__json_decode($v){}

    /**
     * secret 类型为 加解密类型，某些场景下 需要将内容进行加密后存进数据库
     * @param $str
     */

    final protected function __type__secret_encode($v){}
    final protected function __type__secret_decode($v){}
    

    //-------------------------------------------------------------------------------
    /**
     * ArrayAccess 接口相关
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset){
        return isset($this->___modBaseAttr['attr_data'][$offset]);
    }
    public function offsetGet($offset){
        return isset($this->___modBaseAttr['attr_data'][$offset]) ? $this->___modBaseAttr['attr_data'] : null;
    }
    public function offsetSet($offset, $value){
        $this->___modBaseAttr['attr_data'][$offset]=$value;
    }
    public function offsetUnset($offset){
        unset($this->___modBaseAttr['attr_data'][$offset]);
    }
    //-------------------------------------------------------------------------------




}