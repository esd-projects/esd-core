<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/13
 * Time: 18:16
 */

namespace ESD\Plugins\Aop;


use ESD\Core\Event\Event;

class AopEvent extends Event
{
    const type = "AopEvent";

    /**
     * AopEvent constructor.
     */
    public function __construct()
    {
        parent::__construct(self::type, "");
    }
}