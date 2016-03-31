<?php
namespace Csp\core;
use \Csphp;

class CspEvent{

    //监听器执行方式 可以立即 或者延迟到程序退出后执行
    const EXEC_IMMEDIATELY      = 'EXEC_IMMEDIATELY';
    const EXEC_DELAY            = 'EXEC_DELAY';

    /**
     * 事情监听函数，所有监听行为都挂载在这个队列中，当有事件发生时 在这个队列中查找相关的监听函数，并执行
     * eventName=>func(CspEvent $event){}
     * @var array
     */
    public static $eventListenerMap = array();

    /**
     * 如果事件是可延迟的，则会在程序退出的时候执行
     * @var array
     */
    public static $shutdownJobs = array();


    /**
     * @var null|array 触发事件时，所传递的数据，在监听器中 可获取
     */
    public $data    = null;
    /**
     * @var null|object 事件处罚者对象
     */
    public $sender  = null;

    /**
     * @var string 当前事件名称
     */
    public $eventName = null;


    /**
     * 构造一个事件对象，
     * @param string $eventName
     * @param null   $data
     * @param null   $sender
     */
    public function __construct($eventName, $data=null, $sender=null){
        $this->eventName = $eventName;
        $this->data      = $data;
        $this->sender    = $sender == null ? Csphp::app() : $sender;
    }

    /**
     * 获取当前的应用对象
     * @return \Csphp
     */
    public function app(){
        return Csphp::app();
    }

    /**
     * 获取事件发生时传递的数据
     * @return array|null
     */
    public function getData(){
        return $this->data;
    }

    /**
     * 获取事件触发者对象，如果未指定，则直接返回 app
     * @return null|object
     */
    public function getSender(){
        return $this->sender;
    }

    /**
     * 返回当前事件名
     * @return string
     */
    public function getEventName(){
        return $this->eventName;
    }

    /**
     * 触发一个事件
     * @param string $eventName     事件名称
     * @param null $eventData       事件发生的相当数据，将传递给监听器的第一个参数
     * @param null $eventSender     事件触发者对象，将传递给监听器的第二个参数
     */
    public static function fire($eventName, $eventData=null, $eventSender=null){
        foreach(self::getLister($eventName) as $event){

            list($eventExecType, $eventLister) = $event;

            $eventArgs = array(new self($eventName,$eventData, $eventSender));

            //延迟执行的监听器注册到 $shutdownJobs 中，
            if($eventExecType===self::EXEC_DELAY){
                self::registerShutdownFucnForDelayEvent();

                self::$shutdownJobs[] = function() use($eventLister, $eventArgs) {
                    call_user_func_array($eventLister, $eventArgs);
                };
            }else{
                call_user_func_array($eventLister, $eventArgs);
            }
        }
        return true;
    }

    /**
     * 获取某个事件，目前已注册的所有监听器
     * @param string $eventName
     * @return array 如果还没有注册过相关 监听器，则返回空数组
     */
    public static function getLister($eventName){
        if( isset(self::$eventListenerMap[$eventName]) && is_array(self::$eventListenerMap[$eventName]) ){
            return self::$eventListenerMap[$eventName];
        }else{
            return array();
        }
    }

    /**
     * 注册一个立即执行的监听器
     * @param string $eventName
     * @param null   $eventListener func(CspEvent $event){}
     */
    public static function on($eventName, $eventListener){
        if(!isset(self::$eventListenerMap[$eventName])){
            self::$eventListenerMap[$eventName]=array();
        }
        self::$eventListenerMap[$eventName][] = array(self::EXEC_IMMEDIATELY, $eventListener);
    }

    /**
     * 注册一个延迟执行的监听器，将会在系统PHP进程退出前执行
     * @param string    $eventName  事件名
     * @param callable  $eventListener func(CspEvent $event){}
     */
    public static function delayOn($eventName, $eventListener){
        if(!isset(self::$eventListenerMap[$eventName])){
            self::$eventListenerMap[$eventName]=array();
        }

        self::$eventListenerMap[$eventName][] = array(self::EXEC_DELAY, $eventListener);
    }

    /**
     * 有延迟事件被 触发时，注册 shutdown jobs
     */
    public static function registerShutdownFucnForDelayEvent(){
        static $isRegistered= false;
        if(!$isRegistered){
            register_shutdown_function(array(__CLASS__,'doShutdownListers'));
        }
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
