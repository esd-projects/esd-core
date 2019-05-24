<?php

namespace ESD\ExampleClass;

use ESD\Server\Co\CoServer;
use ESD\Core\Exception;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Server\Process\Process;
use ESD\Core\Server\Server;

class SwooleApplication extends CoServer
{

    public function __construct(Server $server, string $defaultPortClass, string $defaultProcessClass)
    {
        parent::__construct($server, $defaultPortClass, $defaultProcessClass);

        //TODO add plugin
    }

    /**
     * @param AbstractPlugin $plugin
     * @throws Exception
     */
    public function addPlug(AbstractPlugin $plugin)
    {
        $this->server->getPlugManager()->addPlug($plugin);
    }


    /**
     * 所有的配置插件已初始化好
     * @return mixed
     */
    public function configureReady()
    {
        return;
    }

    public function onStart()
    {
        var_dump("start");
    }

    public function onShutdown()
    {
        // TODO: Implement onShutdown() method.
    }

    public function onManagerStart()
    {
        // TODO: Implement onManagerStart() method.
    }

    public function onManagerStop()
    {
        // TODO: Implement onManagerStop() method.
    }

    public function onWorkerError(Process $process, int $exit_code, int $signal)
    {
        // TODO: Implement onWorkerError() method.
    }
}