<?php
namespace Csp\base;
use Csp\core\CspException;
use Csp\core\CspResponse;
use Csphp;

class CspBaseComponent {

    public function __construct() {

    }

    /**
     * @param $optArr
     *
     * @throws \Csp\core\CspException
     */
    public function setInitOptions($optArr){
        throw new CspException("Comp must implements setInitOtions  method to use options init ");
    }
}