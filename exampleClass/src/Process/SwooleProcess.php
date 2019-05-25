<?php


namespace ESD\ExampleClass\Process;


use ESD\Core\Message\Message;
use ESD\Core\Server\Process\Process;
use Psr\Log\LoggerInterface;

class SwooleProcess extends Process
{
    /**
     * @var LoggerInterface
     */
    protected $log;
    /**
     * 在onProcessStart之前，用于初始化成员变量
     * @return mixed
     */
    public function init()
    {
        $this->log = DIGet(LoggerInterface::class);
    }

    public function onProcessStart()
    {
        $this->log->info("onProcessStart");
    }

    public function onProcessStop()
    {
        $this->log->info("onProcessStop");
    }

    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        // TODO: Implement onPipeMessage() method.
    }
}