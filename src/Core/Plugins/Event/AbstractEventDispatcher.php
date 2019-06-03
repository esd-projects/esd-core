<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/3
 * Time: 10:10
 */

namespace ESD\Core\Plugins\Event;


use ESD\Core\Order\Order;

abstract class AbstractEventDispatcher extends Order
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcherManager;

    public function __construct()
    {
        $this->eventDispatcherManager = DIGet(EventDispatcher::class);
    }

    /**
     * 处理发送的消息
     * @param Event $event
     * @return mixed
     */
    abstract public function handleEventFrom(Event $event);

    /**
     * 派发消息
     * @param Event $event
     * @return mixed
     */
    abstract public function dispatchEvent(Event $event): bool;
}