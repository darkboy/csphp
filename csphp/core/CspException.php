<?php
namespace Csp\core;
use \Csphp;


class CspException extends \Exception{
    //框架自身相关的异常
    const CORE_EXCEPTION            = 'CORE_EXCEPTION';
    //文件无法找到的异常
    const FILE_NOT_FOUND_EXCEPTION  = 'FILE_NOT_FOUND_EXCEPTION';
    //路由错误的异常
    const ROUTE_NOT_FOUND_EXCEPTION = 'ROUTE_NOT_FOUND_EXCEPTION';
    //数据库查询异常
    const DB_QUERY_EXCEPTION        = 'DB_QUERY_EXCEPTION';
    //文件IO异常
    const FILE_IO_EXCEPTION         = 'FILE_IO_EXCEPTION';

    public $exceptionType           = 'CORE_EXCEPTION';
    private $msgData = null;
    public function __construct($message, $code = 0,$type='CORE_EXCEPTION'){
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
        echo '<pre>';
        echo Csphp::trace($this->getTrace());
        print_r(Csphp::router()->routeInfo);
        return "Exception Code is: [{$this->code}]; Msg: {$this->message}";
    }

    /**
     * 获取异常类型
     * @return string
     */
    public function getExceptionType(){
        return $this->exceptionType;
    }

    public function handler(){

    }
}



