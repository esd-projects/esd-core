<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/14
 * Time: 11:29
 */

namespace ESD\Plugins\AnnotationsScan;


use ESD\Core\Event\Event;

class ScanEvent extends Event
{
    const type = "ScanEvent";
    public function __construct()
    {
        parent::__construct(self::type, "");
    }
}