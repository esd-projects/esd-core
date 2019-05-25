<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/24
 * Time: 15:46
 */

namespace ESD\Server\Swoole\Event;


use ESD\Core\Event\EventCall;
use ESD\Core\Event\EventDispatcher;

class EventCallImpl implements EventCall
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $once;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var
     */
    private $callback;

    public function __construct(EventDispatcher $eventDispatcher, string $type, bool $once = false)
    {
        $this->type = $type;
        $this->once = $once;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isOnce(): bool
    {
        return $this->once;
    }

    /**
     * @param bool $once
     */
    public function setOnce(bool $once): void
    {
        $this->once = $once;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    public function call(callable $fuc, $timeout = 5)
    {
        $this->callback = $fuc;
    }

    public function destroy()
    {

    }

    /**
     * @param $data
     */
    public function send($data)
    {
        if ($this->once) {
            $this->eventDispatcher->remove($this->type, $this);
        }
        $fuc = $this->callback;
        $fuc($data);
    }
}