<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/15
 * Time: 14:36
 */

namespace ESD\Plugins\EasyRoute\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 * Class ModelAttribute
 * @package ESD\Plugins\EasyRoute\Annotation
 */
class ModelAttribute extends Annotation
{
    /**
     * @var string
     */
    public $value;
}