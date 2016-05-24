<?php
namespace Csp\comp\db;

use Csp\base\CspBaseComponent;
use Csp\comp\db\CspCompDBMysqli;
use Csp\core\CspException;
use Csphp;



class CspCompDBConnection extends CspBaseComponent {

    /**
     * mysqli 实例对象池
     * @var array
     */
    public static  $mysqlConnectionPoll = [];

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取一个数据库连接
     * 默认使用 配置为 is_default 的数据库配置，无法找到时 使用第一个
     * @param string $dsnName       需要连接的 dsn 名称
     *
     * @return CspCompDBMysqli
     */
    public static function getConnection($dsnName=null){

        if(isset(self::$mysqlConnectionPoll[$dsnName])){
            return self::$mysqlConnectionPoll[$dsnName];
        }

        if($dsnName===null){
            $dsn = self::getDefaultDsnConfig();
            $dsnName = $dsn['name'];
            $dbConfig = $dsn['cfg'];
        }else{
            $dbConfig = Csphp::appCfg('mysql/'.$dsnName, []);
        }
        if(empty($dbConfig)){
            throw new CspException("Error db config, dsnName: mysql/{$dsnName} ");
        }
        self::$mysqlConnectionPoll[$dsnName] = new CspCompDBMysqli($dbConfig);
        return self::$mysqlConnectionPoll[$dsnName];
    }

    /**
     * 获取默认的数据库 DSN 配置 配置为 is_default 的第一个，
     *
     * @return array ['name'=>$n,'cfg'=>$v];
     */
    public static function getDefaultDsnConfig(){
        static $defaultDsnName=null;
        if($defaultDsnName!==null){
            return Csphp::appCfg('mysql/'.$defaultDsnName, []);
        }
        $mysqlConfig = Csphp::appCfg('mysql', []);
        $dbConfig = null;
        $firstName = null;
        foreach($mysqlConfig as $n=>$v){
            if(isset($v['is_default']) && $v['is_default']){
                $defaultDsnName = $n;
                print_r($v);
                return ['name'=>$n,'cfg'=>$v];
            }
            if(!$firstName){
                $firstName = $n;
            }
        }
        $defaultDsnName = $firstName;
        return ['name'=>$firstName, 'cfg'=>$mysqlConfig[$firstName]];
    }


}