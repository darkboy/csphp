<?php
namespace Csp\core;
use \Csphp as Csphp;


class CspException extends \Exception{

    public function __construct($message, $code = 0){
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

    // 自定义字符串输出的样式
    public function __toString() {
        echo '<pre>';
        echo Csphp::trace($this->getTrace());
        return "Exception Code is: [{$this->code}]; Msg: {$this->message}";
    }
}



