<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/14
 * Time: 16:58
 */

namespace ESD\Plugins\EasyRoute\Annotation;


use ESD\Plugins\AnnotationsScan\Annotation\Component;

/**
 * @Annotation
 * @Target("CLASS")
 * Class RestController
 * @package ESD\Plugins\EasyRoute\Annotation
 */
class Controller extends Component
{
    /**
     * 路由前缀
     * @var string
     */
    public $value = "";

    /**
     * 默认方法
     * @var string
     */
    public $defaultMethod;
    /**
     * 端口访问类型，http,ws,tcp,udp，如果为空数组则无限制
     * @var array
     */
    public $portTypes = [];
    /**
     * 端口名，如果为空数组则无限制
     * @var array
     */
    public $portNames = [];
}