<?php
namespace App\libs;
use Csphp;
use Csp\core\CspException;

class libDemo{
    public function __construct() {
        echo __CLASS__.' init';
    }

    public function handler(){

    }

    public function setInitOptions($opts){
        echo __CLASS__.' options '.json_encode($opts);
    }

}