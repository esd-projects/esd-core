<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/27
 * Time: 17:03
 */

namespace ESD\Plugins\AnnotationsScan;


class ScanReflectionMethod
{
    /**
     * @var \ReflectionMethod
     */
    protected $reflectionMethod;
    /**
     * @var \ReflectionClass
     */
    protected $parentReflectClass;

    public function __construct(\ReflectionClass $parentReflectClass, \ReflectionMethod $reflectionMethod)
    {
        $this->reflectionMethod = $reflectionMethod;
        $this->parentReflectClass = $parentReflectClass;
    }

    /**
     * @return \ReflectionMethod
     */
    public function getReflectionMethod(): \ReflectionMethod
    {
        return $this->reflectionMethod;
    }

    /**
     * @return \ReflectionClass
     */
    public function getParentReflectClass(): \ReflectionClass
    {
        return $this->parentReflectClass;
    }

    public function getName()
    {
        return $this->reflectionMethod->getName();
    }
}