<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/14
 * Time: 17:20
 */

namespace ESD\Plugins\EasyRoute\Annotation;


use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class RequestMapping extends Annotation
{
    public $value;
    public $method = [];
}