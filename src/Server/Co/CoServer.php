<?php
namespace ESD\Server\Co;


use ESD\Server\Co\Beans\Request;
use ESD\Server\Co\Beans\RequestProxy;
use ESD\Server\Co\Beans\Response;
use ESD\Server\Co\Beans\ResponseProxy;
use ESD\Core\Exception\ConfigException;
use ESD\Core\Server\AbstractServer;
use ESD\Core\Server\Beans\ClientInfo;
use ESD\Core\Server\Beans\ServerStats;
use ESD\Core\Server\Beans\WebSocketFrame;
use ESD\Core\Server\Process\Process;
use ESD\Core\Server\ServerProcess\ManagerProcess;
use ESD\Core\Server\ServerProcess\MasterProcess;
use Exception;
use Iterator;
use ReflectionException;
use Swoole\WebSocket\Server as SwooleWebsocketServer;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Server as SwooleServer;

abstract class CoServer extends AbstractServer
{

    /**
     * @var SwooleWebsocketServer
     */
    protected $swooleServer;


    /**
     * 初始化服务
     */
    public function init()
    {
        $this->server->getContainer()->set(Response::class, new ResponseProxy());
        $this->server->getContainer()->set(Request::class, new RequestProxy());
    }

    /**
     * 启动服务
     * @throws Exception
     */
    public function start()
    {
        if ($this->swooleServer == null) {
            throw new Exception("请先调用configure");
        }
        $this->swooleServer->start();
    }

    /**
     * 执行配置
     * @throws ConfigException
     * @throws \ESD\Core\Exception
     * @throws ReflectionException
     */
    public function configure()
    {
        //设置主要进程
        $managerProcess = new ManagerProcess($this->server);
        $masterProcess = new MasterProcess($this->server);
        $this->processManager->setMasterProcess($masterProcess);
        $this->processManager->setManagerProcess($managerProcess);

        //设置进程名称
        Process::setProcessTitle($this->server->getServerConfig()->getName());

        //创建端口实例
        $this->getPortManager()->createPorts();

        //主要端口
        if ($this->portManager->hasWebSocketPort()) {
            foreach ($this->portManager->getPorts() as $serverPort) {
                if ($serverPort->isWebSocket()) {
                    $this->mainPort = $serverPort;
                    break;
                }
            }
            if ($this->server->getServerConfig()->getProxyServerClass() == null) {
                $this->swooleServer = new SwooleWebsocketServer($this->mainPort->getPortConfig()->getHost(),
                    $this->mainPort->getPortConfig()->getPort(),
                    SWOOLE_PROCESS,
                    $this->mainPort->getPortConfig()->getSwooleSockType()
                );
            } else {
                $proxyClass = $this->server->getServerConfig()->getProxyServerClass();
                $this->swooleServer = new $proxyClass();
            }
        } else if ($this->portManager->hasHttpPort()) {
            foreach ($this->portManager->getPorts() as $serverPort) {
                if ($serverPort->isHttp()) {
                    $this->mainPort = $serverPort;
                    break;
                }
            }
            if ($this->server->getServerConfig()->getProxyServerClass() == null) {
                $this->swooleServer = new SwooleHttpServer($this->mainPort->getPortConfig()->getHost(),
                    $this->mainPort->getPortConfig()->getPort(),
                    SWOOLE_PROCESS,
                    $this->mainPort->getPortConfig()->getSwooleSockType()
                );
            } else {
                $proxyClass = $this->server->getServerConfig()->getProxyServerClass();
                $this->swooleServer = new $proxyClass();
            }
        } else {
            $this->mainPort = array_values($this->getPortManager()->getPorts())[0];
            if ($this->server->getServerConfig()->getProxyServerClass() == null) {
                $this->swooleServer = new SwooleServer($this->mainPort->getPortConfig()->getHost(),
                    $this->mainPort->getPortConfig()->getPort(),
                    SWOOLE_PROCESS,
                    $this->mainPort->getPortConfig()->getSwooleSockType()
                );
            } else {
                $proxyClass = $this->server->getServerConfig()->getProxyServerClass();
                $this->swooleServer = new $proxyClass();
            }
        }
        $portConfigData = $this->mainPort->getPortConfig()->buildConfig();
        $serverConfigData = $this->server->getServerConfig()->buildConfig();
        $serverConfigData = array_merge($portConfigData, $serverConfigData);
        $this->swooleServer->set($serverConfigData);
        //多个端口
        foreach ($this->portManager->getPorts() as $serverPort) {
            $serverPort->create($serverPort == $this->mainPort);
        }
        //配置回调
        $this->swooleServer->on("start", [$this, "_onStart"]);
        $this->swooleServer->on("shutdown", [$this, "_onShutdown"]);
        $this->swooleServer->on("workerError", [$this, "_onWorkerError"]);
        $this->swooleServer->on("managerStart", [$this, "_onManagerStart"]);
        $this->swooleServer->on("managerStop", [$this, "_onManagerStop"]);
        $this->swooleServer->on("workerStart", [$this, "_onWorkerStart"]);
        $this->swooleServer->on("pipeMessage", [$this, "_onPipeMessage"]);
        $this->swooleServer->on("workerStop", [$this, "_onWorkerStop"]);
        //配置进程
        $this->processManager->createProcess();
    }

    /**
     * 获取swoole的server类
     * @return SwooleWebsocketServer
     */
    public function getServer()
    {
        return $this->swooleServer;
    }

    /**
     * TCP连接迭代器
     * @return Iterator
     */
    public function getConnections(): Iterator
    {
        return $this->swooleServer->connections;
    }

    /**
     * 获取连接的信息
     * @param int $fd
     * @return ClientInfo
     */
    public function getClientInfo(int $fd): ClientInfo
    {
        return new ClientInfo($this->swooleServer->getClientInfo($fd));
    }

    /**
     * 关闭客户端连接
     * $reset设置为true会强制关闭连接，丢弃发送队列中的数据
     * @param int $fd
     * @param bool $reset
     */
    public function closeFd(int $fd, bool $reset = false)
    {
        $this->swooleServer->close($fd, $reset);
    }

    /**
     * 自动判断是ws还是tcp
     * @param int $fd
     * @param string $data
     */
    public function autoSend(int $fd, string $data)
    {
        $clientInfo = $this->getClientInfo($fd);
        $port = $this->getPortManager()->getPortFromPortNo($clientInfo->getServerPort());
        if ($this->isEstablished($fd)) {
            $this->wsPush($fd, $data, $port->getPortConfig()->getWsOpcode());
        } else {
            $this->send($fd, $data);
        }
    }

    /**
     * 向客户端发送数据
     * @param int $fd 客户端的文件描述符
     * @param string $data 发送的数据
     * @param int $serverSocket 向Unix Socket DGRAM对端发送数据时需要此项参数，TCP客户端不需要填写
     * @return bool 发送成功会返回true
     */
    public function send(int $fd, string $data, int $serverSocket = -1): bool
    {
        return $this->swooleServer->send($fd, $data, $serverSocket);
    }

    /**
     * 发送文件到TCP客户端连接
     * @param int $fd
     * @param string $filename 要发送的文件路径，如果文件不存在会返回false
     * @param int $offset 指定文件偏移量，可以从文件的某个位置起发送数据。默认为0，表示从文件头部开始发送
     * @param int $length 指定发送的长度，默认为文件尺寸。
     * @return bool 操作成功返回true，失败返回false
     */
    public function sendFile(int $fd, string $filename, int $offset = 0, int $length = 0): bool
    {
        return $this->swooleServer->sendfile($fd, $filename, $offset, $length);
    }

    /**
     * 向任意的客户端IP:PORT发送UDP数据包。
     * 必须监听了UDP的端口，才可以使用向IPv4地址发送数据
     * 必须监听了UDP6的端口，才可以使用向IPv6地址发送数据
     * @param string $ip 为IPv4或IPv6字符串，如192.168.1.102。如果IP不合法会返回错误
     * @param int $port 为 1-65535的网络端口号，如果端口错误发送会失败
     * @param string $data 要发送的数据内容，可以是文本或者二进制内容
     * @param int $server_socket 服务器可能会同时监听多个UDP端口，此参数可以指定使用哪个端口发送数据包
     * @return bool
     */
    public function sendToUpd(string $ip, int $port, string $data, int $server_socket = -1): bool
    {
        return $this->swooleServer->sendto($ip, $port, $data, $server_socket);
    }

    /**
     * 检测fd对应的连接是否存在。
     * @param $fd
     * @return bool
     */
    public function existFd($fd): bool
    {
        return $this->swooleServer->exist($fd);
    }

    /**
     * 将连接绑定一个用户定义的UID，可以设置dispatch_mode=5设置以此值进行hash固定分配。可以保证某一个UID的连接全部会分配到同一个Worker进程。
     * @param int $fd
     * @param int $uid
     */
    public function bindUid(int $fd, int $uid)
    {
        $this->swooleServer->bind($fd, $uid);
    }

    /**
     * 得到当前Server的活动TCP连接数，启动时间，accpet/close的总次数等信息。
     * @return ServerStats
     */
    public function stats(): ServerStats
    {
        return new ServerStats($this->swooleServer->stats());
    }

    /**
     * 检测服务器所有连接，并找出已经超过约定时间的连接。如果指定if_close_connection，则自动关闭超时的连接。未指定仅返回连接的fd数组。
     * 调用成功将返回一个连续数组，元素是已关闭的$fd
     * 调用失败返回false
     * @param bool $if_close_connection
     * @return array
     */
    public function heartbeat(bool $if_close_connection = true): array
    {
        return $this->swooleServer->heartbeat($if_close_connection);
    }

    /**
     * 获取最近一次操作错误的错误码。业务代码中可以根据错误码类型执行不同的逻辑。
     * 1001 连接已经被Server端关闭了，出现这个错误一般是代码中已经执行了$serv->close()关闭了某个连接，但仍然调用$serv->send()向这个连接发送数据
     * 1002 连接已被Client端关闭了，Socket已关闭无法发送数据到对端
     * 1003 正在执行close，onClose回调函数中不得使用$serv->send()
     * 1004 连接已关闭
     * 1005 连接不存在，传入$fd 可能是错误的
     * 1007 接收到了超时的数据，TCP关闭连接后，可能会有部分数据残留在管道缓存区内，这部分数据会被丢弃
     * 1008 发送缓存区已满无法执行send操作，出现这个错误表示这个连接的对端无法及时收数据导致发送缓存区已塞满
     * 1202 发送的数据超过了 Co->buffer_output_size 设置
     * @return int
     */
    public function getLastError(): int
    {
        return $this->swooleServer->getLastError();
    }

    /**
     * 设置客户端连接为保护状态，不被心跳线程切断。
     * $value 设置的状态，true表示保护状态，false表示不保护
     * @param int $fd
     * @param bool $value
     */
    public function protect(int $fd, bool $value = true)
    {
        $this->swooleServer->protect($fd, $value);
    }

    /**
     * 确认连接，与enable_delay_receive配合使用。
     * 当客户端建立连接后，并不监听可读事件。
     * 仅触发onConnect事件回调，在onConnect回调中执行confirm确认连接，这时服务器才会监听可读事件，接收来自客户端连接的数据。
     * @param int $fd
     */
    public function confirm(int $fd)
    {
        $this->swooleServer->confirm($fd);
    }

    /**
     * 重启所有Worker/Task进程。
     */
    public function reload()
    {
        $this->swooleServer->reload();
    }

    /**
     * 关闭服务器
     */
    public function shutdown()
    {
        $this->swooleServer->shutdown();
    }

    /**
     * 延后执行一个PHP函数
     * @param callable $callback
     */
    public function defer(callable $callback)
    {
        $this->swooleServer->defer($callback);
    }

    /**
     * 向websocket客户端连接推送数据，长度最大不得超过2M。
     * @param int $fd
     * @param $data
     * @param int $opcode
     * @param bool $finish
     * @return bool
     */
    public function wsPush(int $fd, $data, int $opcode = 1, bool $finish = true): bool
    {
        return $this->swooleServer->push($fd, $data, $opcode, $finish);
    }

    /**
     * 主动向websocket客户端发送关闭帧并关闭该连接
     * @param int $fd
     * @param int $code 关闭连接的状态码，根据RFC6455，对于应用程序关闭连接状态码，取值范围为1000或4000-4999之间
     * @param string $reason 关闭连接的原因，utf-8格式字符串，字节长度不超过125
     * @return bool
     */
    public function wsDisconnect(int $fd, int $code = 1000, string $reason = ""): bool
    {
        return $this->swooleServer->disconnect($fd, $code, $reason);
    }

    /**
     * 检查连接是否为有效的WebSocket客户端连接。
     * 此函数与exist方法不同，exist方法仅判断是否为TCP连接，无法判断是否为已完成握手的WebSocket客户端。
     * @param int $fd
     * @return bool
     */
    public function isEstablished(int $fd): bool
    {
        return $this->swooleServer->isEstablished($fd);
    }

    /**
     * 打包WebSocket消息
     * 返回打包好的WebSocket数据包，可通过Socket发送给对端
     * @param WebSocketFrame $webSocketFrame 消息内容
     * @param bool $mask 是否设置掩码
     * @return string
     */
    public function wsPack(WebSocketFrame $webSocketFrame, $mask = false): string
    {
        return $this->swooleServer->pack($webSocketFrame->getData(), $webSocketFrame->getOpcode(), $webSocketFrame->getFinish(), $mask);
    }

    /**
     * 解析WebSocket数据帧
     * 解析失败返回false
     * @param string $data
     * @return WebSocketFrame
     */
    public function wsUnPack(string $data): WebSocketFrame
    {
        return new WebSocketFrame($this->swooleServer->unpack($data));
    }
}