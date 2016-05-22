<?php
namespace App\libs;
use Csphp;
use Csp\core\CspException;

class libDemo {

    public $opts = 'init';

    public function __construct() {
        //echo __CLASS__.' init';
    }

    public function handler(){

    }

    public function setInitOptions($opts){
        $this->opts = $opts;
    }

    public function hello(){
        echo 'Hellp in libDemo comp with: '.json_encode($this->opts);
    }

}