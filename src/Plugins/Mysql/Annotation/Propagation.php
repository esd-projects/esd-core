<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/10
 * Time: 10:16
 */

namespace ESD\Plugins\Mysql\Annotation;

/**
 * 传播行为
 * Class Propagation
 * @package ESD\Plugins\Mysql\Annotation
 */
class Propagation
{
    /**
     * 如果当前存在事务，则加入该事务；
     * 如果当前没有事务，则创建一个新的事务。
     */
    const REQUIRED = "REQUIRED";
    /**
     * 如果当前存在事务，则加入该事务；
     * 如果当前没有事务，则以非事务的方式继续运行。
     */
    const SUPPORTS = "SUPPORTS";
    /**
     * 如果当前存在事务，则加入该事务；
     * 如果当前没有事务，则抛出异常。
     */
    const MANDATORY = "MANDATORY";
    /**
     * 创建一个新的事务，如果当前存在事务，则把当前事务挂起。
     */
    const REQUIRES_NEW = "REQUIRES_NEW";
    /**
     * 以非事务方式运行，如果当前存在事务，则把当前事务挂起。
     */
    const NOT_SUPPORTED = "NOT_SUPPORTED";
    /**
     * 以非事务方式运行，如果当前存在事务，则抛出异常。
     */
    const NEVER = "NEVER";
    /**
     * 如果当前存在事务，则创建一个事务作为当前事务的嵌套事务来运行；
     * 如果当前没有事务，则该取值等价于 REQUIRED 。
     */
    const NESTED = "NESTED";
}