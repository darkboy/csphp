<?php
namespace Csp\core;
use \Csphp;


class CspException extends \Exception{

    private $msgData = null;
    public function __construct($message, $code = 0){
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
        return "Exception Code is: [{$this->code}]; Msg: {$this->message}";
    }
}



