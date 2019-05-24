<?php


namespace ESD\Coroutine\Channel;

use ESD\Coroutine\Co;
use SplQueue;

class Channel
{

    const CHANNEL_TYPE_CO = 1;

    const CHANNEL_TYPE_SYNC = 2;

    /**
     * 容量
     * @var int
     */
    protected $capacity;

    /**
     * @var \Swoole\Coroutine\Channel
     */
    private $swooleChannel;

    /**
     * @var SplQueue
     */
    private $queueChannel;

    /**
     * @var Callable
     */
    protected $callback = null;

    protected $channelType = self::CHANNEL_TYPE_CO;

    public function __construct(int $capacity = 1)
    {
        $this->capacity = $capacity;
        if(Co::isCoroutine()) {
            $this->channelType = self::CHANNEL_TYPE_CO;
            $this->swooleChannel = new \Swoole\Coroutine\Channel($capacity);
        } else {
            $this->channelType = self::CHANNEL_TYPE_SYNC;
            $this->queueChannel = new SplQueue();
        }
    }

    public function isCoroutine()
    {
        return $this->channelType == self::CHANNEL_TYPE_CO;
    }

    /**
     * 从通道中读取数据。
     * 返回值可以是任意类型的PHP变量，包括匿名函数和资源
     * 通道并关闭时，执行失败返回false
     * @param float $timeout 指定超时时间，浮点型，单位为秒，最小粒度为毫秒，在规定时间内没有生产者push数据，将返回false
     * @return mixed
     */
    public function pop(float $timeout = 0)
    {
        if ($this->isCoroutine()) {
            return $this->swooleChannel->pop($timeout);
        } else {
            if ($this->isEmpty()) {
                return null;
            }
            return $this->queueChannel->dequeue();
        }
    }

    /**
     * 从通道中读取数据。
     * 返回值可以是任意类型的PHP变量，包括匿名函数和资源
     * 通道并关闭时，执行失败返回false
     * @param callable $callback
     */
    public function popLoop(callable $callback)
    {
        if ($this->isCoroutine()) {
            while(true) {
                $result = $this->swooleChannel->pop();
                if ($result === false) {
                    break;
                }
                $callback($result);
            }
        } else {
            $this->callback = $callback;
        }
    }

    /**
     * 向通道中写入数据。
     * 为避免产生歧义，请勿向通道中写入空数据，如0、false、空字符串、null
     * @param mixed $data 可以是任意类型的PHP变量，包括匿名函数和资源
     * @param float $timeout 设置超时时间，在通道已满的情况下，push会挂起当前协程，在约定的时间内，如果没有任何消费者消费数据，将发生超时，底层会恢复当前协程，push调用立即返回false，写入失败
     * @return bool
     */
    public function push($data, float $timeout = -1): bool
    {
        if ($this->isCoroutine()) {
            return $this->swooleChannel->push($data, $timeout);
        } else {
            if ($this->isFull()) {
                return false;
            }
            $this->queueChannel->enqueue($data);

            return true;
        }

    }

    /**
     * 是否为空
     * @return bool
     */
    public function isEmpty() : bool
    {
        if ($this->isCoroutine()) {
            return $this->swooleChannel->isEmpty();
        } else {
            return $this->queueChannel->isEmpty();
        }
    }

    /**
     * 是否已满
     * @return bool
     */
    public function isFull() : bool
    {
        if ($this->isCoroutine()) {
            return $this->swooleChannel->isFull();
        } else {
            return $this->queueChannel->count() == $this->capacity;
        }
    }

    /**
     * 关闭通道
     * @return void
     */
    public function close()
    {
        if ($this->isCoroutine()) {
            $this->swooleChannel->close();
        } else {
            $this->queueChannel = new SplQueue();
        }
    }

    /**
     * 获取通道中元素数量
     * @return int
     */
    public function length() : int
    {
        if ($this->isCoroutine()) {
            return $this->swooleChannel->length();
        } else {
            return $this->queueChannel->count();
        }
    }

    /**
     * 获取通道的状态
     * @return ChannelStats
     */
    public function getStats(): ChannelStats
    {
        if ($this->isCoroutine()) {
            return new ChannelStats($this->swooleChannel->stats());
        } else {
            return new ChannelStats([
                'queue_num' => $this->length()
            ]);
        }
    }

    /**
     * 构造函数中设定的容量会保存在此，不过如果设定的容量小于1则此变量会等于1
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->capacity;
    }

    /**
     * 获取错误码
     * @return int
     */
    public function getErrCode(): int
    {
        if ($this->isCoroutine()) {
            return $this->swooleChannel->errCode;
        } else {
            return 0;
        }
    }


}