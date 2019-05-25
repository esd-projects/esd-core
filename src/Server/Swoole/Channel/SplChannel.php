<?php


namespace ESD\Server\Swoole\Channel;


use ESD\Core\Channel\Channel;
use SplQueue;

class SplChannel implements Channel
{

    /**
     * 容量
     * @var int
     */
    protected $capacity;

    /**
     * @var SplQueue
     */
    private $queueChannel;

    public function __construct(int $capacity = 1)
    {
        $this->capacity = $capacity;
        $this->queueChannel = new SplQueue();
    }

    public function push($data, float $timeout = -1): bool
    {
        if ($this->isFull()) {
            return false;
        }
        $this->queueChannel->enqueue($data);

        return true;
    }

    public function pop(float $timeout = 0)
    {
        if ($this->isEmpty()) {
            return null;
        }
        return $this->queueChannel->dequeue();
    }

    public function length(): int
    {
        return $this->queueChannel->count();
    }

    public function isEmpty(): bool
    {
        return $this->queueChannel->isEmpty();
    }

    public function isFull(): bool
    {
        return $this->queueChannel->count() == $this->capacity;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function close()
    {

    }

    public function popLoop(callable $callback)
    {
        // TODO: Implement popLoop() method.
    }
}