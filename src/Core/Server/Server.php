<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/15
 * Time: 13:43
 */

namespace ESD\Core\Server;

use DI\Container;

use DI\DependencyException;
use ESD\Core\Exception\ConfigException;
use ESD\Coroutine\Context\Context;
use ESD\Core\Exception;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Core\Plugins\Config\ConfigContext;
use ESD\Core\Plugins\Config\ConfigPlugin;
use ESD\Core\Plugins\DI\DIPlugin;
use ESD\Core\Plugins\Event\EventDispatcher;
use ESD\Core\Plugins\Event\EventPlugin;
use ESD\Core\Plugins\Logger\Logger;
use ESD\Core\Plugins\Logger\LoggerPlugin;
use ESD\Core\Server\Config\ServerConfig;
use ReflectionException;

/**
 * Class Co
 * 封装了Server对象
 * @package ESD\BaseServer\Co
 */
class Server
{
    /**
     * @var static
     */
    public static $instance;

    /**
     * 是否启动
     * @var bool
     */
    public static $isStart = false;

    /**
     * 服务器配置
     * @var ServerConfig
     */
    protected $serverConfig;

    /**
     * 是否已配置
     * @var bool
     */
    protected $configured = false;


    /**
     * @var Context
     */
    protected $context;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var ConfigContext
     */
    protected $configContext;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var PluginInterfaceManager
     */
    protected $plugManager;

    /**
     * @var PluginInterfaceManager
     */
    protected $basePlugManager;

    /**
     * @var AbstractServer
     */
    protected $abstractServer;

    /**
     * 这里context获取不到任何插件，因为插件还没有加载
     * Co constructor.
     * @param ServerConfig $serverConfig
     * @param string $defaultAbstractServerClass
     * @param string $defaultPortClass
     * @param string $defaultProcessClass
     * @throws ConfigException
     * @throws DependencyException
     * @throws Exception
     * @throws ReflectionException
     * @throws \Exception
     */
    public function __construct(ServerConfig $serverConfig,
                                string $defaultAbstractServerClass,
                                string $defaultPortClass,
                                string $defaultProcessClass
        )
    {
        self::$instance = $this;
        $this->serverConfig = $serverConfig;

        date_default_timezone_set('Asia/Shanghai');

        if(!class_exists($defaultAbstractServerClass)) {
            throw new \Exception("$defaultAbstractServerClass not exists!");
        }

        $this->abstractServer = new $defaultAbstractServerClass($this, $defaultPortClass, $defaultProcessClass);
        if(!$this->abstractServer instanceof AbstractServer) {
            throw new \Exception("$defaultAbstractServerClass must extend AbstractServer!");
        }

        $this->context = new Context();
        Context::registerContext(Context::SERVER_CONTEXT, $this->context);

        $this->basePlugManager = new PluginInterfaceManager($this);

        //初始化默认插件添加DI/Config/Logger/Event插件
        $this->basePlugManager->addPlug(new DIPlugin());
        $this->basePlugManager->addPlug(new ConfigPlugin());
        $this->basePlugManager->addPlug(new LoggerPlugin());
        $this->basePlugManager->addPlug(new EventPlugin());
        $this->basePlugManager->order();
        $this->basePlugManager->init($this->context);
        $this->basePlugManager->beforeServerStart($this->context);

        //合并ServerConfig配置
        $this->serverConfig->merge();

        //获取上面这些后才能初始化plugManager
        $this->plugManager = new PluginInterfaceManager($this);

        //配置DI容器
        $this->container->set(Logger::class, $this->log);
        $this->container->set(\Monolog\Logger::class, $this->log);
        $this->container->set(EventDispatcher::class, $this->eventDispatcher);
        $this->container->set(ConfigContext::class, $this->configContext);
        $this->container->set(PluginInterfaceManager::class, $this->getPlugManager());

        // 初始化
        $this->abstractServer->init();
        $this->container->set($defaultAbstractServerClass, $this->abstractServer);

        set_exception_handler(function ($e) {
            $this->log->error($e);
        });

        print_r($serverConfig->getBanner() . "\n");
    }


    /**
     * 启动服务
     */
    public function start()
    {
        $this->abstractServer->start();
    }

    /**
     * 添加插件和添加配置只能在configure之前
     * 配置服务
     * @throws ConfigException
     * @throws Exception
     */
    public function configure()
    {
        //先生成部分配置
        $this->abstractServer->getPortManager()->mergeConfig();
        $this->abstractServer->getProcessManager()->mergeConfig();

        //插件排序此时不允许添加插件了
        $this->plugManager->order();
        $this->plugManager->init($this->context);
        //调用所有插件的beforeServerStart
        $this->plugManager->beforeServerStart($this->context);
        //锁定配置
        $this->setConfigured(true);

        // 执行服务配置
        $this->abstractServer->configure();

        // 配置完成
        $this->abstractServer->configureReady();

        //打印配置
        $this->log->debug("打印配置:\n" . $this->configContext->getCacheContainYaml());
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * @param bool $configured
     */
    public function setConfigured(bool $configured): void
    {
        $this->configured = $configured;
    }

    /**
     * @return AbstractServer
     */
    public function getAbstractServer(): AbstractServer
    {
        return $this->abstractServer;
    }

    /**
     * @return PluginInterfaceManager
     */
    public function getPlugManager(): PluginInterfaceManager
    {
        return $this->plugManager;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * @return ServerConfig
     */
    public function getServerConfig(): ServerConfig
    {
        return $this->serverConfig;
    }

    /**
     * @return PluginInterfaceManager
     */
    public function getBasePlugManager(): PluginInterfaceManager
    {
        return $this->basePlugManager;
    }

    /**
     * @return Logger
     */
    public function getLog(): Logger
    {
        return $this->log;
    }

    /**
     * @return ConfigContext
     */
    public function getConfigContext(): ConfigContext
    {
        return $this->configContext;
    }

    /**
     * @return Container|null
     */
    public function getContainer(): ?Container
    {
        return $this->container;
    }

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ConfigContext $configContext
     */
    public function setConfigContext(ConfigContext $configContext): void
    {
        $this->configContext = $configContext;
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * @param Logger $log
     */
    public function setLog(Logger $log): void
    {
        $this->log = $log;
    }
}