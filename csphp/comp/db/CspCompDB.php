<?php
namespace Csp\comp\db;
use Csp\base\CspBaseComponent;
use Csp\comp\db\CspcompDBMysqli;

use Csphp;



class CspCompDBConnection extends CspBaseComponent {

    public static  $mysqlConnectionPoll = [];

    public function __construct() {
        parent::__construct();
    }

    /**
     *
     * @param string $dsnName
     * @param string $dbConfig
     */
    public static function getConnection($dsnName='default', $dbConfig=[]){
        if(!isset(self::$mysqlConnectionPoll[$dsnName])){
            self::$mysqlConnectionPoll[$dsnName] = new CspcompDBMysqli($dbConfig);
        }
        return self::$mysqlConnectionPoll[$dsnName];
    }

}