<?php

namespace ESD\ExampleClass;

use ESD\Core\Exception;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Server\Config\ServerConfig;
use ESD\Core\Server\Process\Process;
use ESD\ExampleClass\Port\SwoolePort;
use ESD\ExampleClass\Process\SwooleProcess;
use ESD\Plugins\AnnotationsScan\AnnotationsScanPlugin;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\Cache\CachePlugin;
use ESD\Plugins\EasyRoute\EasyRoutePlugin;
use ESD\Plugins\Mysql\MysqlPlugin;
use ESD\Plugins\Pack\PackPlugin;
use ESD\Plugins\Redis\RedisPlugin;
use ESD\Plugins\Validate\ValidatePlugin;
use ESD\Server\Co\CoServer;
use ESD\Server\Swoole\SwooleServer;

class SwooleApplication extends SwooleServer
{
    /**
     * SwooleApplication constructor.
     * @param ServerConfig|null $serverConfig
     * @param string $portClass
     * @param string $processClass
     * @throws Exception
     * @throws \ESD\Core\Config\ConfigException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function __construct(?ServerConfig $serverConfig = null,
                                string $portClass = SwoolePort::class,
                                string $processClass = SwooleProcess::class)
    {
        if ($serverConfig == null) {
            $serverConfig = new ServerConfig();
        }
        parent::__construct($serverConfig, $portClass, $processClass);

        $this->getPlugManager()->addPlug(new CachePlugin());
        $this->getPlugManager()->addPlug(new RedisPlugin());
        $this->getPlugManager()->addPlug(new MysqlPlugin());
        $this->getPlugManager()->addPlug(new PackPlugin());
        $this->getPlugManager()->addPlug(new ValidatePlugin());
        $this->getPlugManager()->addPlug(new AopPlugin());
        $this->getPlugManager()->addPlug(new AnnotationsScanPlugin());
        $this->getPlugManager()->addPlug(new EasyRoutePlugin());
        $this->configure();
        $this->start();
    }


    /**
     * @param AbstractPlugin $plugin
     * @throws Exception
     */
    public function addPlug(AbstractPlugin $plugin)
    {
        $this->getPlugManager()->addPlug($plugin);
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