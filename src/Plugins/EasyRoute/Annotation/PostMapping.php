<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/14
 * Time: 17:17
 */

namespace ESD\Plugins\EasyRoute\Annotation;


use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class PostMapping extends RequestMapping
{
    public $method = ["post"];
}