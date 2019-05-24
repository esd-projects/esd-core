<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace ESD\Core\Plugins\Logger;

use DI\DependencyException;
use ESD\Coroutine\Context\Context;
use ESD\Core\Exception;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Plugins\Config\ConfigChangeEvent;
use ESD\Core\Plugins\Config\ConfigPlugin;
use ESD\Core\Plugins\Event\EventDispatcher;
use ESD\Core\Server\Server;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use ReflectionException;

/**
 * Log 插件加载器
 * Class EventPlug
 * @package ESD\BaseServer\Plugins\Event
 */
class LoggerPlugin extends AbstractPlugin
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var StreamHandler
     */
    private $handler;
    /**
     * @var LoggerConfig
     */
    private $loggerConfig;

    /**
     * LoggerPlugin constructor.
     * @param LoggerConfig|null $loggerConfig
     * @throws DependencyException
     * @throws ReflectionException
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
     * @throws Exception
     * @throws \Exception
     */
    private function buildLogger(Context $context)
    {
        $this->logger = new Logger($this->loggerConfig->getName());
        $formatter = new LineFormatter($this->loggerConfig->getOutput(),
            $this->loggerConfig->getDateFormat(),
            $this->loggerConfig->isAllowInlineLineBreaks(),
            $this->loggerConfig->isIgnoreEmptyContextAndExtra());
        $serverConfig = Server::$instance->getServerConfig();
        if ($serverConfig->isDaemonize()) {
            $this->handler = new RotatingFileHandler($serverConfig->getBinDir() . "/logs/" . $this->loggerConfig->getName() . ".log",
                $this->loggerConfig->getMaxFiles(),
                Logger::DEBUG);
        } else {
            $this->handler = new StreamHandler('php://stderr', Logger::DEBUG);
        }
        $this->handler->setFormatter($formatter);
        $this->logger->pushProcessor(new SwooleProcessor($this->loggerConfig->isColor()));
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->pushHandler($this->handler);
        $context->add("logger", $this->logger);
        Server::$instance->setLog($this->logger);
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->loggerConfig->merge();
        $this->buildLogger($context);
        $this->handler->setLevel($this->loggerConfig->getLevel());
    }

    /**
     * 在进程启动前
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        //监控配置更新
        goWithContext(function () use ($context) {
            /**
             * @var $eventDispatcher EventDispatcher
             */
            $eventDispatcher = $context->getDeepByClassName(EventDispatcher::class);

            $eventDispatcher->listen(ConfigChangeEvent::ConfigChangeEvent, function() {
                $this->loggerConfig->merge();
                $this->handler->setLevel($this->loggerConfig->getLevel());
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