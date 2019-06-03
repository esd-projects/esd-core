<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/3
 * Time: 9:51
 */

namespace ESD\Core\Plugins\Event;


use ESD\Core\Server\Server;

/**
 * 进程 事件派发器
 * Class TypeEventDispatcher
 * @package ESD\Core\Plugins\Event
 */
class ProcessEventDispatcher extends AbstractEventDispatcher
{
    const type = "ProcessEventDispatcher";

    public function __construct()
    {
        parent::__construct();
        $this->atBefore(TypeEventDispatcher::class);
    }

    /**
     * 处理发送的消息
     * @param Event $event
     */
    public function handleEventFrom(Event $event)
    {
        if (Server::$instance == null || Server::$instance->getProcessManager() == null) {
            $event->setFromInfo($this->getName(), -1);
        } else {
            $event->setFromInfo($this->getName(), Server::$instance->getProcessManager()->getCurrentProcessId());
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::type;
    }

    /**
     * 派发消息
     * @param Event $event
     * @return bool
     */
    public function dispatchEvent(Event $event): bool
    {
        $toProcess = $event->getToInfo($this->getName());
        if ($toProcess == null) {
            return true;
        } else {
            $next = false;
            foreach ($toProcess as $processId) {
                $process = Server::$instance->getProcessManager()->getProcessFromId($processId);
                if ($processId == Server::$instance->getProcessManager()->getCurrentProcessId()) {
                    $next = true;
                } else {
                    Server::$instance->getProcessManager()->getCurrentProcess()->sendMessage(new EventMessage($event), $process);
                }
            }
            //处理本进程的,如果没有本进程的可以不继续传递处理
            return $next;
        }
    }
}