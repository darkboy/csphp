<?php
namespace Csp\comp\db;

use Csp\base\CspBaseComponent;
use Csp\comp\db\CspCompDBMysqli;
use Csphp;



class CspCompDBConnection extends CspBaseComponent {

    public static  $mysqlConnectionPoll = [];

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取一个数据库连接
     * @param string $dsnName
     * @param string $dbConfig
     * @return CspCompDBMysqli
     */
    public static function getConnection($dsnName='default'){
        if(!isset(self::$mysqlConnectionPoll[$dsnName])){
            $dbConfig = Csphp::appCfg('mysql/'.$dsnName, []);
            self::$mysqlConnectionPoll[$dsnName] = new CspCompDBMysqli($dbConfig);
        }
        return self::$mysqlConnectionPoll[$dsnName];
    }

    public static function tmp(){

    }
}