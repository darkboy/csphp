<?php
namespace Csp\base;
use \Csphp as Csphp;

class CspBaseControler {

    public $jsonpCallbackName = null;
    public function __construct(){

    }


    public function apiRst($rst, $code=0, $msg, $tips=''){
        return Csphp::warpJsonApiData($rst, $code, $msg, $tips);
    }

    public function ajaxRst($rst, $code=0, $msg, $tips=''){
        return Csphp::warpJsonApiData($rst, $code, $msg, $tips);
    }

    public function jsonpRst($rst, $code=0, $msg, $tips=''){
        return $this->$jsonpCallbackName.'('.Csphp::warpJsonApiData($rst, $code, $msg, $tips).');';
    }


}
