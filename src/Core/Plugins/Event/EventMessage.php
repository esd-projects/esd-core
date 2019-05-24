<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 9:28
 */

namespace ESD\Core\Plugins\Event;


use ESD\Core\Server\Process\Message\Message;

/**
 * Event消息
 * Class Event
 * @package ESD\BaseServer\Plugins\Event
 */
class EventMessage extends Message
{
    const type = "@event";

    public function __construct(Event $event)
    {
        parent::__construct(self::type, $event);
    }

    public function getEvent(): Event
    {
        return $this->getData();
    }
}