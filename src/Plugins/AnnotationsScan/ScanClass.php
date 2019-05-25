<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/14
 * Time: 11:30
 */

namespace ESD\Plugins\AnnotationsScan;


use Doctrine\Common\Annotations\CachedReader;
use ReflectionClass;
use ReflectionMethod;

class ScanClass
{
    private $annotationMethod = [];
    private $annotationClass = [];
    /**
     * @var CachedReader
     */
    private $cachedReader;

    public function __construct(CachedReader $cachedReader)
    {
        $this->cachedReader = $cachedReader;
    }

    /**
     * @return array
     */
    public function getAnnotationClass(): array
    {
        return $this->annotationClass;
    }

    public function addAnnotationClass($annClass, ReflectionClass $reflectionClass)
    {
        $this->annotationClass[$annClass][] = $reflectionClass;
    }

    public function addAnnotationMethod(string $annClass, ReflectionMethod $reflectionMethod)
    {
        $this->annotationMethod[$annClass][] = $reflectionMethod;
    }

    /**
     * 通过注解类名获取相关类
     * @param $annClass
     * @return ReflectionClass[]
     */
    public function findClassesByAnn($annClass)
    {
        return $this->annotationClass[$annClass] ?? [];
    }

    /**
     * @return CachedReader
     */
    public function getCachedReader(): CachedReader
    {
        return $this->cachedReader;
    }

    /**
     * 通过注解类名获取相关方法
     * @param $annClass
     * @return ReflectionMethod[]
     */
    public function findMethodsByAnn($annClass)
    {
        return $this->annotationMethod[$annClass] ?? [];
    }

    /**
     * @return array
     */
    public function getAnnotationMethod(): array
    {
        return $this->annotationMethod;
    }


}