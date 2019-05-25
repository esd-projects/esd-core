<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/15
 * Time: 13:07
 */

namespace ESD\Plugins\EasyRoute\Annotation;


use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class PathVariable extends Annotation
{
    /**
     * @var string
     */
    public $value;
    /**
     * @var string|null
     */
    public $param;
    /**
     * @var bool
     */
    public $required = false;
}