<?php
namespace Csp\core;
use \Csphp as Csphp;

class CspEvent{
    //
    const EVN_PHP_SHUTDOWN      = 'SYS:EVENT_SHUTDOWN';
    const EVN_PHP_EXIT          = 'SYS:EVENT_EXIT';

    const EVN_APP_START         = 'SYS:EVENT_APP_START';
    const EVN_APP_END           = 'SYS:EVN_APP_END';

    const EVN_APP_RENDER_START  = 'SYS:EVN_APP_RENDER_START';
    const EVN_APP_RENDER_END    = 'SYS:EVN_APP_RENDER_END';

    const EVN_APP_COMP_START    = 'SYS:EVN_APP_COMP_START';
    const EVN_APP_COMP_END      = 'SYS:EVN_APP_COMP_END';


    //监听器执行方式 可以立即 或者延迟到程序退出后执行
    const EXEC_IMMEDIATELY      = 'EXEC_IMMEDIATELY';
    const EXEC_DELAY            = 'EXEC_DELAY';

    /**
     * 事情监听函数，所有监听行为都挂载在这个队列中，当有事件发生时 在这个队列中查找相关的监听函数
     * eventName=>func($eventDdata, eventSender=null){}
     * @var array
     */
    public static $eventListenerMap = array();

    /**
     * 如果事件是可延迟的，则会在程序退出的时候执行
     * @var array
     */
    public static $shutdownJobs = array();
    public function __construct(){

    }

    /**
     * 触发一个事件
     * @param $eventName            事件名称
     * @param null $eventData       事件发生的相当数据，将传递给监听器的第一个参数
     * @param null $eventSender     事件触发者对象，将传递给监听器的第二个参数
     */
    public static function fire($eventName, $eventData=null, $eventSender=null){
        if(isset(self::$eventListenerMap[$eventName]) && is_array(self::$eventListenerMap[$eventName])){
            foreach(self::$eventListenerMap[$eventName] as $event){
                list($eventExecType, $eventLister) = $event;
                $eventArgs = array($eventData, $eventSender);
                //延迟执行的监听器注册到 $shutdownJobs 中，
                if($eventExecType===self::EXEC_DELAY){
                    self::$shutdownJobs[] = function() use($eventLister, $eventArgs) {
                        call_user_func_array($eventLister, $eventArgs);
                    };
                }else{
                    call_user_func_array($eventLister, $eventArgs);
                }
            }
        }
    }

    /**
     * 注册一个立即执行的监听器
     * @param $eventName
     * @param null $eventListener
     */
    public static function on($eventName, $eventListener){
        if(!isset(self::$eventListenerMap[$eventName])){
            self::$eventListenerMap[$eventName]=array();
        }
        self::$eventListenerMap[$eventName][] = array(self::EXEC_IMMEDIATELY, $eventListener);
    }

    /**
     * 注册一个延迟执行的监听器
     * @param $eventName
     * @param null $eventData
     * @param null $eventSender
     */
    public static function delayOn($eventName, $eventListener){
        if(!isset(self::$eventListenerMap[$eventName])){
            self::$eventListenerMap[$eventName]=array();
        }

        self::$eventListenerMap[$eventName][] = array(self::EXEC_DELAY, $eventListener);
    }


    /**
     * 执行已注册的延迟监听器
     */
    public static function doShutdownListers(){
        foreach(self::$shutdownJobs as $func){
            $func();
        }
    }


}
