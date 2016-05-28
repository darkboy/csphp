<?php
namespace Csp\core;
use ReflectionClass;
use ReflectionParameter;
use ReflectionMethod;
use Closure;
use ArrayAccess;

/**
 * 简易的 IOC 容器，实现 构造器依赖注入
 *
 * Class CspIocContainer
 *
 * @package Csp\core
 */
interface CspServicesInterface {

    /**
     * 服务 必须实现 getServicesName 接口，
     * 告诉 IOC容器，需要
     * @return mixed
     */
    public function getServicesName();
}