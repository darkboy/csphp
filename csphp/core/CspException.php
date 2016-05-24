<?php
namespace Csp\core;
use \Csphp;


class CspException extends \Exception{
    //路由错误的异常 404
    const NOT_FOUND_EXCEPTION       = 'NOT_FOUND_EXCEPTION';
    //参数输入异常，不符合标准
    const PARAM_INPUT_EXCEPTION     = 'PARAM_INPUT_EXCEPTION';
    //访问控制权限异常
    const ACL_DENY_EXCEPTION        = 'ACL_DENY_EXCEPTION';
    //其它运行时相关的异常
    const RUNTIME_EXCEPTION         = 'RUNTIME_EXCEPTION';

    /**
     * 当前异常类别
     * @var string
     */
    public $exceptionType           = 'RUNTIME_EXCEPTION';

    private $msgData = null;
    private $msg        = null;
    protected  $code       = null;
    private $context    = null;

    /**
     * 异常构造函数
     * @param string $message
     * @param int    $code
     * @param string $type
     */
    public function __construct($message, $code = 0, $type='RUNTIME_EXCEPTION'){
        $this->msgData = $message;

        if(is_array($message)) {
            foreach (array('message', 'tips', 'msg','error', 'info' ,'MSG', 'text', 'content') as $msgkey) {
                if(isset($message[$msgkey]) && is_string($message[$msgkey])){
                    $message = $message[$msgkey];
                    break;
                }
            }

            foreach (array('code', 'errno', 'errorno', 'status') as $codekey) {
                if(isset($message[$codekey]) && is_numeric($message[$codekey])) {
                    $code = $message[$codekey];
                    break;
                }
            }
        }
        if(is_array($message)){
            $message = join(' ; ',$message);
        }
        $this->exceptionType = $type;
        parent::__construct($message, $code);
    }

    /**
     * 获取异常消息数据，可能是自定义的数据
     *
     * @return null|string
     */
    public function getMsgData(){
        return $this->msgData;
    }
    // 自定义字符串输出的样式
    public function __toString() {
        echo '<pre style="color: darkblue;">',Csphp::trace($this->getTrace());
        if($this->exceptionType==self::NOT_FOUND_EXCEPTION){
            print_r(Csphp::router()->routeInfo);
        }
        return "{$this->exceptionType} ; Code is: [{$this->code}]; Msg is: {$this->message} Ctx is:".json_encode($this->context);
    }

    /**
     * 获取异常类型
     * @return string
     */
    public function getExceptionType(){
        return $this->exceptionType;
    }

    /**
     *
     */
    public function handler(){
        switch($this->exceptionType){
            case self::NOT_FOUND_EXCEPTION :
                $this->notFound404();
                break;
            case self::PARAM_INPUT_EXCEPTION :
                break;
            case self::RUNTIME_EXCEPTION :

                break;

        }
    }

    private function notFound404(){

    }
}



