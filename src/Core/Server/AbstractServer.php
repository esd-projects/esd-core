<?php


namespace ESD\Core\Server;


use ESD\Core\Exception\ConfigException;
use ESD\Core\Plugins\Event\ApplicationEvent;
use ESD\Core\Server\Interfaces\ISwooleServer;
use ESD\Core\Server\Interfaces\IWebsocketServer;
use ESD\Core\Server\Port\IServerPort;
use ESD\Core\Server\Port\PortManager;
use ESD\Core\Server\Process\Process;
use ESD\Core\Server\Process\ProcessManager;
use ReflectionException;
use Throwable;

abstract class AbstractServer implements ISwooleServer, IWebsocketServer
{

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var ProcessManager
     */
    protected $processManager;

    /**
     * @var PortManager
     */
    protected $portManager;


    /**
     * @var IServerPort
     */
    protected $mainPort;

    /**
     * AbstractServer constructor.
     * @param Server $server
     * @param string $defaultPortClass
     * @param string $defaultProcessClass
     */
    public function __construct(Server $server,
                                string $defaultPortClass,
                                string $defaultProcessClass
        )
    {
        $this->server = $server;

        $this->portManager = new PortManager($server, $defaultPortClass);
        $this->processManager = new ProcessManager($server, $defaultProcessClass);
    }

    /**
     * 初始化服务
     */
    abstract public function init();

    /**
     * 启动服务
     */
    abstract public function start();

    /**
     * 执行配置
     */
    abstract public function configure();

    /**
     * 所有的配置插件已初始化好
     * @return mixed
     */
    abstract public function configureReady();

    /**
     * 启动
     */
    public function _onStart()
    {
        Server::$isStart = true;
        //发送ApplicationStartingEvent事件
        $this->server->getEventDispatcher()->dispatchEvent(new ApplicationEvent(ApplicationEvent::ApplicationStartingEvent, $this));
        $this->processManager->getMasterProcess()->onProcessStart();
        try {
            $this->onStart();
        } catch (Throwable $e) {
            $this->server->getLog()->error($e);
        }
    }

    /**
     * 添加一个进程
     * @param string $name
     * @param null $processClass 不填写将用默认的
     * @param string $groupName
     * @throws ConfigException
     * @throws ReflectionException
     */
    public function addProcess(string $name, $processClass = null, string $groupName = Process::DEFAULT_GROUP)
    {
        if ($this->server->isConfigured()) {
            throw new ConfigException("配置已锁定，请在调用configure前添加");
        }
        $this->processManager->addCustomProcessesConfig($name, $processClass, $groupName);
    }

    /**
     * 关闭
     */
    public function _onShutdown()
    {
        //发送ApplicationShutdownEvent事件
        $this->server->getEventDispatcher()->dispatchEvent(new ApplicationEvent(ApplicationEvent::ApplicationShutdownEvent, $this));
        try {
            $this->onShutdown();
        } catch (Throwable $e) {
            $this->server->getLog()->error($e);
        }
    }

    public function _onWorkerError($serv, int $worker_id, int $worker_pid, int $exit_code, int $signal)
    {
        $process = $this->processManager->getProcessFromId($worker_id);
        $this->server->getLog()->alert("workerId:$worker_id exitCode:$exit_code signal:$signal");
        try {
            $this->onWorkerError($process, $exit_code, $signal);
        } catch (Throwable $e) {
            $this->server->getLog()->error($e);
        }
    }

    public function _onManagerStart()
    {
        Server::$isStart = true;
        $this->processManager->getManagerProcess()->onProcessStart();
        try {
            $this->onManagerStart();
        } catch (Throwable $e) {
            $this->server->getLog()->error($e);
        }
    }

    public function _onManagerStop()
    {
        $this->processManager->getManagerProcess()->onProcessStop();
        try {
            $this->onManagerStop();
        } catch (Throwable $e) {
            $this->server->getLog()->error($e);
        }
    }

    public function _onWorkerStart($server, int $worker_id)
    {
        Server::$isStart = true;
        $process = $this->processManager->getProcessFromId($worker_id);
        $process->_onProcessStart();
    }

    public function _onPipeMessage($server, int $srcWorkerId, $message)
    {
        $this->processManager->getCurrentProcess()->_onPipeMessage($message, $this->processManager->getProcessFromId($srcWorkerId));
    }

    public function _onWorkerStop($server, int $worker_id)
    {
        $process = $this->processManager->getProcessFromId($worker_id);
        $process->_onProcessStop();
    }

    public abstract function onStart();

    public abstract function onShutdown();

    public abstract function onWorkerError(Process $process, int $exit_code, int $signal);

    public abstract function onManagerStart();

    public abstract function onManagerStop();

    /**
     * 获取swoole的server类
     * @return \Swoole\WebSocket\Server
     */
    abstract public function getServer();

    public function getMainPort()
    {
        return $this->mainPort;
    }

    /**
     * @return ProcessManager
     */
    public function getProcessManager(): ProcessManager
    {
        return $this->processManager;
    }

    /**
     * @return PortManager
     */
    public function getPortManager() : PortManager
    {
        return $this->portManager;
    }
}