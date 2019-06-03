<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/3
 * Time: 9:51
 */

namespace ESD\Core\Plugins\Event;


/**
 * type 事件派发器
 * Class TypeEventDispatcher
 * @package ESD\Core\Plugins\Event
 */
class TypeEventDispatcher extends AbstractEventDispatcher
{
    const type = "TypeEventDispatcher";

    /**
     * 处理发送的消息
     * @param Event $event
     */
    public function handleEventFrom(Event $event)
    {
        //无需处理type的EventFrom
    }

    /**
     * 派发消息
     * @param Event $event
     * @return bool
     */
    public function dispatchEvent(Event $event): bool
    {
        foreach ($event->getToInfo($this->getName()) as $type) {
            $calls = $this->eventDispatcherManager->getEventCalls($type);
            if ($calls == null) continue;
            foreach ($calls as $call) {
                goWithContext(function () use ($call, $event) {
                    $call->send($event);
                });
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::type;
    }

}