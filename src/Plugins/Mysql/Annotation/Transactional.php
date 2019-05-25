<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/10
 * Time: 10:06
 */

namespace ESD\Plugins\Mysql\Annotation;


use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Transactional extends Annotation
{
    public $name = "default";
    /**
     * 隔离级别
     * @var int
     */
    public $isolation = Isolation::DEFAULT;

    /**
     * 传播行为
     * @var int
     */
    public $propagation = Propagation::REQUIRED;

    /**
     * 接收到什么异常会回滚
     * @var string
     */
    public $rollbackFor = \Throwable::class;

    /**
     * 接受到什么异常不会回滚
     * @var null | string
     */
    public $noRollbackFor = null;
}