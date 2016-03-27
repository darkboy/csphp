<?php
namespace Csp\core;
use \Csphp as Csphp;

class CspApplatection{

    public function __constructor(){

    }

    public function run(){
        var_dump(Csphp::$appConfig);
    }

}
