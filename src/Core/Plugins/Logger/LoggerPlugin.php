<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace ESD\Core\Plugins\Logger;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Plugins\Config\ConfigChangeEvent;
use ESD\Core\Plugins\Config\ConfigPlugin;
use ESD\Core\Plugins\Event\EventDispatcher;
use ESD\Core\Server\Server;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

/**
 * Log 插件加载器
 * Class EventPlug
 * @package ESD\Core\Plugins\Event
 */
class LoggerPlugin extends AbstractPlugin
{
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var LoggerConfig
     */
    private $loggerConfig;
    /**
     * @var GoSwooleProcessor
     */
    private $goSwooleProcessor;

    /**
     * LoggerPlugin constructor.
     * @param LoggerConfig|null $loggerConfig
     * @throws \ReflectionException
     */
    public function __construct(?LoggerConfig $loggerConfig = null)
    {
        parent::__construct();
        $this->atAfter(ConfigPlugin::class);
        if ($loggerConfig == null) {
            $loggerConfig = new LoggerConfig();

        }
        $this->loggerConfig = $loggerConfig;
    }

    /**
     * @param Context $context
     * @throws \Exception
     */
    private function buildLogger(Context $context)
    {
        $this->logger = new Logger($this->loggerConfig->getName());
        $formatter = new LineFormatter($this->loggerConfig->getOutput(),
            $this->loggerConfig->getDateFormat(),
            $this->loggerConfig->isAllowInlineLineBreaks(),
            $this->loggerConfig->isIgnoreEmptyContextAndExtra());
        //屏幕打印
        $handler = new StreamHandler('php://stderr', $this->loggerConfig->getLevel());
        $this->logger->pushHandler($handler);
        $handler->setFormatter($formatter);
        $this->goSwooleProcessor = new GoSwooleProcessor($this->loggerConfig->isColor());
        $this->logger->pushProcessor($this->goSwooleProcessor);
        $this->logger->pushProcessor(new GoIntrospectionProcessor());
        DISet(LoggerInterface::class, $this->logger);
        DISet(\Monolog\Logger::class, $this->logger);
        DISet(Logger::class, $this->logger);
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @throws \ESD\Core\Exception
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->loggerConfig->merge();
        $this->buildLogger($context);
        foreach ($this->logger->getHandlers() as $handler) {
            if ($handler instanceof AbstractHandler) {
                $handler->setLevel($this->loggerConfig->getLevel());
            }
        }
        $this->goSwooleProcessor->setColor($this->loggerConfig->isColor());
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @throws \ESD\Core\Exception
     */
    public function beforeProcessStart(Context $context)
    {
        $serverConfig = Server::$instance->getServerConfig();
        if (Server::$instance->getServerConfig()->isDaemonize()) {
            //去除屏幕打印Handler
            $this->logger->popHandler();
            //添加日志Handler
            $handler = new RotatingFileHandler($serverConfig->getBinDir() . "/logs/" . $this->loggerConfig->getName() . ".log",
                $this->loggerConfig->getMaxFiles(),
                $this->loggerConfig->getLevel());
            $this->logger->pushHandler($handler);
            $this->goSwooleProcessor->setColor(false);
        }
        //监控配置更新
        goWithContext(function () use ($context) {
            $eventDispatcher = DIGet(EventDispatcher::class);
            $call = $eventDispatcher->listen(ConfigChangeEvent::ConfigChangeEvent);
            $call->call(function ($result) {
                $this->loggerConfig->merge();
                foreach ($this->logger->getHandlers() as $handler) {
                    if ($handler instanceof AbstractHandler) {
                        $handler->setLevel($this->loggerConfig->getLevel());
                    }
                }
            });
        });
        $this->ready();
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Logger";
    }

    /**
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }
}