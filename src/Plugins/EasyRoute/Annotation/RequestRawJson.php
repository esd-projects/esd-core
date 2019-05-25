<?php
/**
 * Created by PhpStorm.
 * User: anythink
 * Date: 2019/5/17
 * Time: 11:12
 */

namespace ESD\Plugins\EasyRoute\Annotation;


use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class RequestRawJson extends Annotation
{
    /**
     * @var string
     */
    public $value;
}