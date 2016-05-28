<?php
namespace Csp\base;
use Csp\core\CspException;
use Csphp;
use ArrayAccess;

class CspBaseObject implements ArrayAccess {


    /**
     * @var array
     */
    protected $__cspBaseAttr = [
        //aop 特性相关的配置
        'aop'=>[
            'cache' =>[],
            'xhprof'=>[],
            'time'  =>[],
            'trace' =>[]
        ]
    ];
    /**
     * ArrayAccess
     * @var array
     */
    protected $__cspBaseDataAttr=[];

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

    /**
     *
     * @param $method
     *
     * @return bool
     */
    final function isObjectBaseCall($method){
        return in_array(lcfirst($method), 'useCacheAop','useXhprofAop','');
    }

    /**
     *
     * @param $objOrClassName
     */
    public function wrapByCspAopService($objOrClassName){
        
    }
    /**
     * @param $method
     * @param $args
     */
    public function __call($method,$args){
        throw new CspException("Undefined Comp method $method");
    }

    //--------------------------------------------------------------------------------------
    /**
     * ArrayAccess 接口相关
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset){
        return array_key_exists($offset, $this->__cspBaseDataAttr);
    }
    public function offsetGet($offset){
        return isset($this->__cspBaseDataAttr[$offset]) ? $this->__cspBaseDataAttr[$offset] : null;
    }
    public function offsetSet($offset, $value){
        $this->__cspBaseDataAttr[$offset]=$value;
    }
    public function offsetUnset($offset){
        unset($this->__cspBaseDataAttr[$offset]);
    }
    //--------------------------------------------------------------------------------------

}