<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 9:28
 */

namespace ESD\Core\Plugins\Event;

/**
 * 本进程内的事件
 * Class Event
 * @package ESD\Core\Plugins\Event
 */
class Event
{
    /**
     * 事件类型
     * @var string
     */
    private $type;

    /**
     * 事件内容
     * @var mixed
     */
    private $data;

    /**
     * 消息来自的信息
     * @var array
     */
    private $fromInfo = [];

    /**
     * 消息去向的信息
     * @var array
     */
    private $toInfo = [];

    /**
     * 进度
     * @var string
     */
    private $progress;

    public function __construct(string $type, $data)
    {
        $this->type = $type;
        $this->data = $data;
        $this->setToInfo(TypeEventDispatcher::type, [$type]);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int|null
     */
    public function getProcessId(): ?int
    {
        return $this->getFromInfo(ProcessEventDispatcher::type);
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getFromInfo($type)
    {
        return $this->fromInfo[$type] ?? null;
    }

    /**
     * @param $type
     * @param $data
     */
    public function setFromInfo($type, $data): void
    {
        $this->fromInfo[$type] = $data;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getToInfo($type)
    {
        return $this->toInfo[$type] ?? null;
    }

    /**
     * @param $type
     * @param $data
     */
    public function setToInfo($type, $data): void
    {
        $this->toInfo[$type] = $data;
    }

    /**
     * @return string
     */
    public function getProgress(): ?string
    {
        return $this->progress;
    }

    /**
     * @param string $progress
     */
    public function setProgress(string $progress): void
    {
        $this->progress = $progress;
    }
}