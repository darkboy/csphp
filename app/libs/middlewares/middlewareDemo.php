<?php
namespace App\libs\middlewares;
use Csp\core\CspRequest;
use Csphp;
use Csp\core\CspException;

class middlewareDemo{
    public $v1 = '0';
    public $v2 = '0';
    public function __construct() {
    }

    /**
     * @param \Csp\core\CspRequest $request
     * @param                      $next
     * @param string               $v1
     * @param string               $v2
     *
     * @return mixed
     */
    public function handler(CspRequest $request, $next, $v1='default1', $v2='default2'){

        echo "In middlerware demo handler start v1 $v1 : {$this->v1} v2 $v2 :{$this->v2} \n";
        $r = $next($request);
        echo "In middlerware demo handler end\n";
        return $r;
    }

    public function customHandler(CspRequest $request, $next, $v1='default1', $v2='default2'){

        echo "In middlerware demo customHandler start v1 $v1 : {$this->v1} v2 $v2 :{$this->v2}  \n";
        $r = $next($request);
        echo "In middlerware demo customHandler end\n";
        return $r;

    }



    public function setInitOptions($opts){
        $this->v1 = isset($opts['v1']) ? $opts['v1'] : 'default_opt1';
        $this->v2 = isset($opts['v2']) ? $opts['v2'] : 'default_opt2';
    }

}