<?php
namespace Csp\comp;
use \Csp\base\CspBaseControler;
use \Csp\base\CspBaseComponent;
use \Csphp;


/**
 *
 * Class CspCompMcrypt
 * @package Csp\comp
 */
class CspCompMemcache extends CspBaseComponent {

    public function __construct() {
        parent::__construct();
    }

    public function start(){
        //Csphp::request();
    }

    public $mc     = FALSE;
    public $enable = FALSE;
    public $keyPre = 'mc:';

    public $mcName  = "MC0";

    //配合DB事务的相关属性，可以将某段区间的MC操作清除，以达到rollback的目的
    public static $keylogForTrans = array();        //事务期间操作过的KEY
    public static $isRunnningTrans = false;         //是否正在运行事务

    //---------------------------------------------------------
    /**
     * 根据缓存名称，获取MC实例
     * @param      $kname
     *
     * @return mixed
     */
    public function getMcByKname($kname){
        $kOpt   = $this->getKnameOpt($kname);
        if(empty($kOpt) || !is_array($kOpt)){
            trigger_error('Can not find cache config:  '.$kname, E_USER_ERROR);
            APP::trace();
        }

        $gCacheName = '';
        if(isset($kOpt['gname']) && !empty($kOpt['gname'])){
            $gCacheName = $kOpt['gname'];
        }
        $this->mcName = $kOpt['mc'];
        $mcName = $kOpt['mc'];//MC 组名

        $mc = new groupMemcacheComp(V('-:mc/servers/'.$mcName));
        $mc->keyPre .= $kname.":";

        if($gCacheName){
            return new groupMemcacheComp($mc, $gCacheName);
        }else{
            return $mc;
        }

    }
    //获取一个缓存配置，CACHE ALL 专用
    public function getKnameOpt($kname){
        return V('-:mc/cache_call/'.$kname);
    }
    //---------------------------------------------------------
    //初始化链接
    public function init($config){

        //缓存MC链接
        static $mcServers = array();
        //var_dump($config);
        if(is_array($config) && !empty($config['servers'])) {
            $servHost = trim($config['servers']);
            if(isset($mcServers[$servHost])){
                $this->mc = $mcServers[$servHost];
                $connect = true;
            }else{
                $this->cmdLog(__FUNCTION__, $servHost);
                if (!extension_loaded('memcached')) {
                    $this->mc = new Memcache;
                }else{
                    $this->mc = new Mcached();
                }

                $servers = explode('|', $servHost);
                $connect = FALSE;

                foreach ($servers as $server) {
                    if (empty($server)) {continue;}
                    $param      = explode(':', $server);
                    //print_r($param);exit;
                    $connect    = $connect || @$this->mc->addServer($param[0], $param[1], $config['pconnect']);
                }
                //echo "reach...2";exit;
                //APP::dump($config);
                //保证最少有一个可用
                if (!$connect) {
                    APP::LOG('error', 'compent memcache add server error, config: '.json_encode($config));
                }else{
                    $mcServers[$servHost] = $this->mc;
                }
            }


            $this->enable = $connect ? TRUE : FALSE;
            $this->keyPre = $config['keyPre'];
        }else{
            trigger_error('compent memcache init error, config: '.json_encode($config), E_USER_ERROR);
        }

    }

    public function get($key){
        $key = $this->key($key);
        $this->cmdLog(__FUNCTION__, $key);

        if(!is_array($key)){
            return $this->mc->get($key);
        }else{
            $rst = array();
            $data = $this->mc->get($key);
            if(empty($data) || !is_array($data)) return $data;
            $keyPreLen = strlen($this->keyPre);
            foreach($data as $ks=>$d){
                $k = substr($ks,$keyPreLen);
                $rst[$k] = $d;
            }
            return $rst;
        }
    }

    public function set($key, $value, $ttl = 0){
        $key = $this->key($key);
        $this->cmdLog(__FUNCTION__, $key);

        $rst = $this->mc->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
        if (!$rst) {
            APP::LOG('error',"MC SET ERROR key: $key => ".json_encode($value));
        }
        return $rst;
    }

    public function add($key, $value, $ttl = 0){
        $key = $this->key($key);
        $this->cmdLog(__FUNCTION__, $key);

        $rst = $this->mc->add($key, $value, MEMCACHE_COMPRESSED, $ttl);
        if (!$rst) {
            //APP::LOG('error',"MC ADD ERROR key: $key => ".json_encode($value));
        }
        return $rst;
    }

    public function delete($key){
        $key = $this->key($key);
        $this->cmdLog(__FUNCTION__, $key);
        $rst = $this->mc->delete($key, 0);
        if (!$rst) {
            //APP::LOG('error',"MC DELETE ERROR key: $key ");
        }
        return $rst;
    }

    public function increment($key, $value=1){
        $key = $this->key($key);
        $this->cmdLog(__FUNCTION__, $key);
        $rst = $this->mc->increment($key, $value);
        if (!$rst) {
            APP::LOG('error',"MC increment ERROR key: $key ");
        }
        return $rst;
    }
    public function decrement($key, $value=1){
        $key = $this->key($key);
        $this->cmdLog(__FUNCTION__, $key);

        $rst = $this->mc->decrement($key, $value);
        if (!$rst) {
            APP::LOG('error', "MC decrement ERROR key: $key ");
        }
        return $rst;
    }

    //清空当前MC 服务器
    public function flush(){
        $this->cmdLog(__FUNCTION__, "");
        return $this->mc->flush();
    }

    public function key($key){
        if(!is_array($key)) {
            return $this->keyPre.$key;
        }else{
            foreach($key as $i=>$k){
                $key[$i] = $this->keyPre.$k;
            }
            return $key;
        }
    }

    public function test(){
        echo "REACH DEMO COM\n";
    }

    //设置或者查看MC KEY LOG
    public function cmdLog($cmd=null, $key=null){
        static $cmds=array();
        if(APP_IS_DEBUG){}
        //如果开启了 MC 事务标志，则记录事务期间的写操作
        if(self::$isRunnningTrans && in_array($cmd,array('add','set','decrement','increment'))){
            self::transKeyLog($this->mcName,$key);
        }
        if($cmd){
            $cmds[] = $cmd."\t".(is_array($key) ? json_encode($key) : $key);
            return true;
        }else{
            return $cmds;
        }

    }
    //----
    //配合DB 和 MOD  的事务功能，目前，实现方式为 把中间的CACHE删除 不支持事务嵌套
    public static function startTransation(){
        self::$isRunnningTrans = true;
    }
    //事务期间的key日志
    public static function transKeyLog($mcName,$key){
        if(self::$isRunnningTrans){
            if(!isset(self::$keylogForTrans[$mcName])){
                self::$keylogForTrans[$mcName] = array();
            }

            if(is_array($key)){
                foreach($key as $k){
                    self::$keylogForTrans[$mcName][] = $k;
                }
            }else{
                self::$keylogForTrans[$mcName][] = $key;
            }

        }
    }
    public static function getTransKeyLog(){
        return self::$keylogForTrans;
    }
    public static function rollback(){
        $rollbackLog = array();
        //print_r(self::$keylogForTrans);
        foreach(self::$keylogForTrans as $mcName => $ks){
            $mcObj = new memcache_compent(V('-:mc/servers/'.$mcName));
            foreach($ks as $k){
                $rollbackLog[] = "DELETE {$mcName} {$k}";
                $mcObj->mc->delete($k, 0);
            }
        }
        //print_r($rollbackLog);//调试时，可打印回滚日志
    }
    public static function endTrans(){
        self::$isRunnningTrans  = false;
        self::$keylogForTrans   = array();
    }
    //--
}


/**
 * 重写Memcached类，以兼容支持Memcache
 */
if(class_exists('Memcached')){
    class Mcached extends \Memcached{

        public function __construct(){
            parent::__construct();
            //$this->setOption(Memcached::OPT_SERIALIZER, Memcached::SERIALIZER_PHP); //序列化
            //$this->setOption(Memcached::OPT_COMPRESSION, false);
        }

        public function get($keys){
            if(is_array($keys)){
                return (parent::getMulti($keys));
            }else{
                return (parent::get($keys));
            }
        }

        public function set($key, $value, $zip= 0, $ttl = 0){
            return (parent::set($key, $value,$ttl));
        }

        public function add($key, $value, $zip= 0, $ttl = 0){
            return (parent::add($key, $value, $ttl));
        }

    }
}


/**
 * Class groupMemcacheComp
 * 缓存组
 */
class groupMemcacheComp extends CspCompMemcache {
    public $mcCom = null;
    public $gname = '';//组名
    public $dataKeyPre = "gc:";
    public $verSufix = ":__gcv__";
    public function __construct($mc=null, $gname=null){
        if ($mc) $this->init($mc, $gname);
    }

    public function init($mc, $gname){
        $this->mcCom = $mc;
        $this->gname = $gname;
    }

    public function set($key, $value, $ttl){
        $verKey  = $this->verKey($key);
        $dataKey = $this->dataKey($key);

        $ver = $this->mcCom->get($verKey);
        $ver.="";
        if(empty($ver)){
            $ver = microtime(true);
            $this->mcCom->set($verKey, $ver, $ttl+864000);
        }
        $data = array('ver'=>"$ver", 'data'=>$value);
        //print_r($data);APP::trace();
        return $this->mcCom->set($dataKey, $data, $ttl);
    }

    public function get($key){
        $verKey  = $this->verKey();
        $dataKey = $this->dataKey($key);

        $isMutiGet = is_array($dataKey);//是否批量获取

        $keys = $isMutiGet ? array_merge($dataKey,array($verKey)) : array($verKey, $dataKey);
        $data = $this->mcCom->get($keys);
        if(!$isMutiGet){
            if(empty($data) || !is_array($data) || !isset($data[$verKey]) || !isset($data[$dataKey]) || $data[$verKey]!=$data[$dataKey]['ver'] )
            {
                //echo "\n\n key : $dataKey === $key ";print_r($data);
                return false;
            }
            //var_dump($data[$dataKey]['data']);
            return $data[$dataKey]['data'];
        }else{
            $rst = array();
            $ver = "";
            if(isset($data[$verKey])){
                $ver = $data[$verKey];
                unset($data[$verKey]);
            }
            if(empty($ver)) {return $rst;}//不存在版本信息，直接返回空

            $keyPreLen = strlen($this->dataKeyPre);

            foreach($data as $dk=>$d){
                if($d['ver']==$ver){
                    $k = substr($dk,$keyPreLen);
                    $rst[$k] = $d['data'];
                }

            }
            //print_r($data);
            return $rst;
        }


    }

    public function delete($key){
        $dataKey = $this->dataKey($key);
        return $this->mcCom->delete($dataKey);
    }

    //flush整个缓存组
    public function updateCacheGroup(){
        $verKey  = $this->verKey();
        return $this->mcCom->delete($verKey);
    }
    public function flushCacheGroup(){
        return $this->updateCacheGroup();
    }
    //缓存组的版本号 key
    public function verKey(){
        return $this->gname.$this->verSufix;
    }

    public function dataKey($key){
        if(is_array($key)){
            $ks = array();
            foreach($key as $k){
                $ks[] = $this->dataKeyPre.$k;
            }
            return $ks;
        }else{
            return $this->dataKeyPre.$key;
        }

    }

}
