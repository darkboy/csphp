<?php
namespace Csp\core;
use ReflectionClass;
use ReflectionParameter;
use ReflectionMethod;
use Closure;
use ArrayAccess;

/**
 * IOC 容器，实现 构造器依赖注入
 *
 *
 * Class CspIocContainer
 *
 * @package Csp\core
 */
class CspIocContainer {

    //已经实例化好的 类 ，key=>$obj, key 为 类名，或者别名
    private $instance   = [];
    //已定义的，对象生产方法，key=>className | closure
    private $binds      = [];
    //记录别名映射
    private $alias      = [];

    //构造函数
    public function __construct(){

    }

    /**
     * setter
     *
     * @param $k
     * @param $c
     */
    public function __set($k, $c) {
        $this->bind($k, $c);
    }

    /**
     * getter
     *
     * @param $k
     *
     * @return object
     * @throws \Csp\core\CspException
     */
    public function __get($k) {
        return $this->build($k);
    }

    /**
     *
     * @param      $className
     * @param null $target
     */
    public function bind($className, $target=null){
        $this->binds[$className] = $target ? $target : $className;
    }

    /**
     * @param $className
     *
     * @return bool
     */
    public function has($className){
        return isset($this->binds[$className]);
    }

    /**
     * 自动绑定（Autowiring）自动解析（Automatic Resolution）
     *
     * @param string $className
     * @param string $constructParams 传递给构造函数的参数，数字索引表示第几个参数，名称索引则表示参数名
     *
     * @return object
     * @throws CspException
     */
    public function build($className, $constructParams=[], $isShare=false) {

        // 如果是闭包函数（closures）, 则直接执行返回
        if ($className instanceof Closure) {
            return call_user_func_array($className, $constructParams);
        }

        /** @var ReflectionClass $reflector */
        $reflector = new ReflectionClass($className);

        // 检查类是否可实例化, 排除抽象类abstract和对象接口interface
        if (!$reflector->isInstantiable()) {
            throw new CspException("Can't instantiate this.");
        }

        /** @var ReflectionMethod $constructor 获取类的构造函数 */
        $constructor = $reflector->getConstructor();

        // 若无构造函数，直接实例化并返回
        if (is_null($constructor)) {
            return new $className;
        }

        // 取构造函数参数,通过 ReflectionParameter 数组返回参数列表
        $parameters = $constructor->getParameters();

        // 递归解析构造函数的参数
        $dependencies = $this->getDependencies($parameters, $constructParams);

        // 创建一个类的新实例，给出的参数将传递到类的构造函数。
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * 通过参数列表，获取依赖，自动生成用户没有提供的构造函数参数
     *
     * @param array $parameters         构造函数的参数元信息
     * @param array $constructParams    应用提供的类初始化参数
     *
     * @return array
     * @throws CspException
     */
    public function getDependencies($parameters, $constructParams=[]) {
        $dependencies = [];

        /** @var ReflectionParameter $parameter */
        foreach ($parameters as $idx=>$parameter) {
            //如果给定的 构造参数是数字索引，则表示传递的是对应 $idx 的参数
            if(array_key_exists($idx, $constructParams)){
                $dependencies[$idx]=$constructParams[$idx];
                continue;
            }
            if(array_key_exists($parameter->name, $constructParams)){
                $dependencies[$idx]=$constructParams[$parameter->name];
                continue;
            }


            /** @var ReflectionClass $dependency */
            $dependency = $parameter->getClass();

            if (is_null($dependency)) {
                // 是变量,有默认值则设置默认值
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                // 是一个类，递归解析
                $dependencies[] = $this->build($dependency->name);
            }
        }

        return $dependencies;
    }

    /**
     * 处理非对象类的参数，如果有默认值则返回
     *
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     */
    public function resolveNonClass(ReflectionParameter $parameter) {
        // 有默认值则返回默认值
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        return null;
    }
}