<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 9:20
 */

namespace ESD\Core\Plugins\Event;

use ESD\Coroutine\Channel\Channel;
use ESD\Core\Plugins\Logger\Logger;
use ESD\Core\Server\Process\Process;
use ESD\Core\Server\Server;

/**
 * 事件派发器
 * Class EventDispatcher
 * @package ESD\BaseServer\Plugins\Event
 */
class EventDispatcher
{
    private $eventChannels = [];
    /**
     * @var Logger
     */
    private $log;

    /**
     * @var Server
     */
    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->log = $server->getContext()->getDeepByClassName(Logger::class);
    }

    /**
     * Registers an event listener at a certain object.
     *
     * @param string $type
     * @param callable $callback
     * @param EventChannel|null $channel
     * @param bool $once 是否仅仅一次
     */
    public function listen($type, callable $callback, $channel = null, $once = false)
    {
        if (!array_key_exists($type, $this->eventChannels)) {
            $this->eventChannels [$type] = [];
        }
        if ($channel == null) {
            $channel = new EventChannel($this, $type, $once);
        }
        array_push($this->eventChannels[$type], $channel);
        $channel->popLoop($callback);
    }

    /**
     * Removes an event listener from the object.
     *
     * @param string $type
     * @param Channel $channel
     */
    public function remove($type, Channel $channel)
    {
        if ($channel != null) $channel->close();
        if (array_key_exists($type, $this->eventChannels)) {
            $index = array_search($channel, $this->eventChannels [$type]);
            if ($index !== null) {
                unset ($this->eventChannels [$type] [$index]);
            }
            $numListeners = count($this->eventChannels [$type]);
            if ($numListeners == 0) {
                unset ($this->eventChannels [$type]);
            }
        }
    }

    /**
     * Removes all event listeners with a certain type, or all of them if type is null.
     * Be careful when removing all event listeners: you never know who else was listening.
     *
     * @param string $type
     */
    public function removeAll($type = null)
    {
        if ($type) {
            unset ($this->eventChannels [$type]);
        } else {
            $this->eventChannels = array();
        }
    }

    /**
     * send event to process
     * @param Event $event
     * @param Process ...$toProcess
     */
    public function dispatchProcessEvent(Event $event, Process ... $toProcess)
    {
        $processManager = Server::$instance->getAbstractServer()->getProcessManager();
        if ($processManager == null) {
            $this->dispatchEvent($event);
            return;
        }

        $event->setProcessId($processManager->getCurrentProcessId());
        if ($toProcess == null) {
            $this->dispatchEvent($event);
        }
        foreach ($toProcess as $process) {
            $processManager->getCurrentProcess()->sendMessage(new EventMessage($event), $process);
        }
    }

    /**
     * Dispatches an event to all objects that have registered listeners for its type.
     * If an event with enabled 'bubble' property is dispatched to a display object, it will
     * travel up along the line of parents, until it either hits the root object or someone
     * stops its propagation manually.
     *
     * @param Event $event
     */
    public function dispatchEvent(Event $event)
    {
        $processManager = Server::$instance->getAbstractServer()->getProcessManager();
        if ($processManager != null) {
            $event->setProcessId($processManager->getCurrentProcessId());
        }
        if (!array_key_exists($event->getType(), $this->eventChannels)) {
            return; // no need to do anything
        }
        $this->invokeEvent($event);
    }

    /**
     * @private
     * Invokes an event on the current object.
     * This method does not do any bubbling, nor
     * does it back-up and restore the previous target on the event. The 'dispatchEvent'
     * method uses this method internally.
     *
     * @param Event $event
     */
    private function invokeEvent($event)
    {
        if (array_key_exists($event->getType(), $this->eventChannels)) {
            $channels = $this->eventChannels [$event->getType()];
        } else {
            return;
        }
        foreach ($channels as $channel) {
            if ($channel instanceof EventChannel) {
                goWithContext(function () use ($channel, $event) {
                    $channel->push($event);
                });
            }
        }
    }

}