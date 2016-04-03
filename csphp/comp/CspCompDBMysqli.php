<?php
namespace Csp\comp;
use \Csp\base\CspBaseControler;
use \Csp\base\CspBaseComponent;
use \Csphp;



class CspCompDBMysqli extends CspBaseComponent {

    /**
     * 链接类型
     */
    const LINK_TYPE_AUTO    = 0;
    const LINK_TYPE_READ    = 1;
    const LINK_TYPE_WRITE   = 2;

    /**
     * @var array
     *
     * 配置格式:
     *
     * $cfg['dbName']=array(
        'host'	 => DB_HOST_W,
        'port'	 => DB_PORT,
        'user'	 => DB_USER,
        'pwd'	 => DB_PASSWD,
        'charset'=> DB_CHARSET,
        'tbpre'	 => DB_PREFIX,
        'dbName' => DB_NAME,
        'slaves' => array(
            array(
                'host'	 => DB_HOST_R1
            ),
            array(
                'host'	 => DB_HOST_R2
            ),

        )

        );
     */
    public $dbConfg     = array();
    /**
     * 链接池 ip:port=>link
     * @var array
     */
    public $linkPoll    = array();
    public $curLinkType = self::LINK_TYPE_AUTO;

    /**
     * 当前请求执行过的 SQL
     * @var array
     */
    public static $sqlLogs  = array();
    public $lastSql   = '';


    public function __construct() {
        parent::__construct();
    }
    private function getLink($type=self::LINK_TYPE_AUTO){}

    public  function getWriteLink(){

    }
    public  function getReadLink(){

    }
    /**
     * 强制当前实例 使用主库
     */
    public function useWriteConnect(){
        //0 自动 ，1 读库，2 写
        $this->curLinkType = self::LINK_TYPE_WRITE;
        return $this;
    }
    //强制当前实例 使用从库
    public function useReadConnect(){
        //0 自动 ，1 读库，2 写
        $this->curLinkType = self::LINK_TYPE_READ;
        return $this;
    }
    //使用当前实例使用自助链接方式
    public function useAutoConnect(){
        //0 自动 ，1 读库，2 写
        $this->curLinkType=0;
        return $this;
    }

    /**
     * 通过sql检测一条语句是 读还是写
     * @param  string $sql
     * @return int
     */
    public  function pareSqlType($sql){
        $wCmds = array('insert', 'update', 'delete', 'replace', 'alter', 'create', 'drop', 'rename', 'truncate');
        if ($sql !== '') {
            $sql = explode(' ', substr((string)$sql, 0, 10));
        }

        return ($sql === '' || !in_array(strtolower($sql[0]), $wCmds)) ? self::LINK_TYPE_READ : self::LINK_TYPE_WRITE;
    }

    public  function exec($sql){}


    //插入或者更新一条记录
    public function insertOrUpdate($table, $insertData, $updateData){
        $sql  = "INSERT  INTO ".$this->getTable($table)." SET ".$this->createSet($insertData);
        $sql .= " ON DUPLICATE KEY UPDATE  ".$this->createSet($updateData);
        return $this->exec($sql, 2, 2);
    }

    //批量添加数据的功能
    public function batchInsert($table, $data, $ignore=false){
        $fields = array_keys($data[0]);
        $fields = $this->escape($fields, '`');
        $values = array();
        foreach($data as $r){
            $values[] = $this->escape($r);
        }
        $ignore = $ignore ? ' IGNORE ' : '';
        $sql = "INSERT $ignore INTO ".$this->getTable($table).$fields." VALUES ".implode(' , ', $values);

        return $this->exec($sql, 2, 2);
    }

    public function create($table, $data, $ignore=FALSE){
        $ignore = $ignore ? ' IGNORE ' : '';
        $sql = "INSERT $ignore INTO ".$this->getTable($table)." SET ".$this->createSet($data);
        return $this->exec($sql, 2, 2);
    }

    public function replace($table, $data, $ignore=FALSE){
        $sql = "REPLACE $ignore INTO ".$this->getTable($table)." SET ".$this->createSet($data);
        return $this->exec($sql, 2, 2);
    }

    public function update($table, $data, $cond){
        $sql = "UPDATE ".$this->getTable($table)." SET ".$this->createSet($data).$this->createWhere($cond);
        return $this->exec($sql, 2, 2);

    }

    public function delete($table, $cond){
        $sql = "DELETE FROM ".$this->getTable($table).$this->createWhere($cond);
        return $this->exec($sql, 2, 2);

    }

    public  function selectRow($table, $cond){}
    public  function selectRows($table, $cond){}
    /**
     * 通用的 AND 条件生成方法
     * @param $cond 成员结构如下四种，不同成员之间 用 AND 连结
     * array(
     *   1=>array(fieldname,opt,optvalue)
     *   1=>array(fieldname,optvalue)
     *   2=>array(condstr)
     *  'fieldname'=>value
     *
     *)
     * @return string
     */
    public function createWhere($cond, $tbAliasName=""){
        if (empty($cond)) return "";
        $where = array();
        foreach($cond as $f=>$v){
            if(is_numeric($f)){
                if (count($v)==1){
                    $where[] = $v[0];//完全自定义的条件
                }else{
                    //解决单个字段 多个条件的问题 array(fieldname,opt[,optvalue])
                    $opt = count($v)>2 ? $v[1] : " = ";
                    $sv  = count($v)>2 ? $v[2] : $v[1];

                    $where[] = "{$tbAliasName}`".$v[0]."` ".$opt." ".$this->escape($sv);
                }

            }else{
                $where[] = "{$tbAliasName}`$f` ".(is_array($v) ? $v[0]." ". $this->escape($v[1])  : ' = '.$this->escape($v));
            }
        }
        return " WHERE ".implode(" AND ", $where);
    }
    /**
     * @param $data array(
     *      field=>array(‘--’) no_escape
     *      field=>xxxx        use escape
     * )
     *
     * @return string
     */
    public function createSet($data){
        if (empty($data)){
            trigger_error("create set data must be not empty ", E_USER_ERROR);
        }

        $dataSet = array();
        foreach($data as $f=>$v){
            if (is_array($v)){
                if (count($v)!=1){
                    trigger_error("error set data format ", E_USER_ERROR);
                }
                $vtmp = array_pop($v);
                $vtmp = $vtmp==="" ? "''" : $vtmp;
                $dataSet[] = "`$f` ".' = '.$vtmp;
            }else{
                $dataSet[] = "`$f` ".' = '.$this->escape($v);
            }

        }
        return " ".implode(" , ", $dataSet);

    }
    /**
     * 根据条件 增加或者减少某个字段的值
     * @param $table
     * @param $field
     * @param $num
     * @param $cond
     */
    public function increment($table, $field, $num, $cond){

        $data = array(
            "$field"=>$this->createIncSetVal($field, $num)
        );
        return $this->update($table, $data, $cond);
    }

    //生成 原子性自增加 或者 减少值
    public function createIncSetVal($field, $num){
        $opt = $num > 0 ? ' + ' : ' - ' ;
        $num = abs($num);
        return array(" `$field` $opt  $num ");
    }



    public  function startTransaction(){}
    public  function commitOrRollback(){}
    public  function getTransationState(){}
    public  function rollback(){}
    public  function commit(){}

    public  function queryCmd(){}
    public  function prepare(){}

    public  function select($fieldListStrOrArr){}
    public  function from($tbName){}
    public  function leftJoin($tbName,$condStr=''){}
    public  function rightJoin($tbName,$condStr=''){}
    public  function join($tbName){}
    public  function where($fieldStr,$data=null){}
    public  function order($fieldName, $orderType){}
    public  function limit($offset,$num=20){
        return " LIMIT ".$offset.", ".$num;
    }
    public  function pager($page, $pageSize){
        $page = max($page*1, 1);
        $pageSize = max($pageSize*1, 1);
        $offset = ($page-1)*$pageSize;
        return $this->limit($offset, $pageSize);
    }
    public  function groupby($field){}

    public  function having($fieldStr,$data){}


    public  function getQueryLogs(){
        return self::$sqlLogs;
    }

    public  function getError(){
        return self::$sqlLogs;
    }

    /**
     * @param $sql
     */
    public function logSql($sql){
        self::$sqlLogs[] = $sql;
        //只保存100条以下的日志
        if(count(self::$sqlLogs)>100){
            array_shift(self::$sqlLogs);
            array_shift(self::$sqlLogs);
        }
        $this->lastSql   = $sql;
        //todo log sql
    }
}