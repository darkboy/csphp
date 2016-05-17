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
class CspCompMcrypt extends CspBaseComponent {
    protected $key = '';
    public function __construct() {
        parent::__construct();
    }

    public function setKey($key){}
    public function encode($s,$key=null){}
    public function decode($s,$key=null){}
}