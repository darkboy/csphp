<?php
namespace Csp\base;
use Csp\core\CspException;
use Csphp;

class CspBaseComponent {

    public function __construct() {

    }


    public function setInitOptions($optArr){
        throw new CspException("Comp must implements setInitOtions  method");
    }
}