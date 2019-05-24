<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/15
 * Time: 16:47
 */

namespace ESD\Server\Co\Port;

use ESD\Core\Exception\ConfigException;
use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Core\Server\Beans\WebSocketCloseFrame;
use ESD\Core\Server\Beans\WebSocketFrame;
use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Server\Server;
use ESD\Coroutine\Context\Context;
use Exception;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server\Port;
use Throwable;

/**
 * AbstractServerPort 端口类
 * Class ServerPort
 * @package ESD\BaseServer\Co
 */
abstract class AbstractServerPort
{
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var PortConfig
     */
    private $portConfig;
    /**
     * @var Server
     */
    private $server;
    /**
     * swoole的port对象
     * @var Port
     */
    private $swoolePort;

    public function __construct(Server $server, PortConfig $portConfig)
    {
        $this->portConfig = $portConfig;
        $this->server = $server;
        $this->context = $this->server->getContext();
    }

    /**
     * @return mixed
     */
    public function getSwoolePort()
    {
        return $this->swoolePort;
    }

    /**
     * 创建端口
     * @param bool $isMain
     * @throws ConfigException
     */
    public function create($isMain = false): void
    {
        $abstractServer = $this->server->getAbstractServer();
        if ($isMain) {
            //端口已经被swoole创建了，直接获取port实例
            $this->swoolePort = $abstractServer->getServer()->ports[0];
            //监听者是server
            $listening = $abstractServer->getServer();
        } else {
            $configData = $this->getPortConfig()->buildConfig();
            $this->swoolePort = $abstractServer->getServer()->listen($this->getPortConfig()->getHost(),
                $this->getPortConfig()->getPort(),
                $this->getPortConfig()->getSwooleSockType());
            $this->swoolePort->set($configData);
            //监听者是端口
            $listening = $this->swoolePort;
        }
        //配置回调
        //TCP
        if ($this->isTcp()) {
            $listening->on("connect", [$this, "_onConnect"]);
            $listening->on("close", [$this, "_onClose"]);
            $listening->on("receive", [$this, "_onReceive"]);
        }
        //UDP
        if ($this->isUDP()) {
            $listening->on("packet", [$this, "_onPacket"]);
        }
        //HTTP
        if ($this->isHttp()) {
            $listening->on("request", [$this, "_onRequest"]);
        }
        //WebSocket
        if ($this->isWebSocket()) {
            $listening->on("message", [$this, "_onMessage"]);
            $listening->on("open", [$this, "_onOpen"]);
            if ($this->getPortConfig()->isCustomHandShake()) {
                $listening->on("handshake", [$this, "_onHandshake"]);
            }
        }
    }

    /**
     * @return PortConfig
     */
    public function getPortConfig(): PortConfig
    {
        return $this->portConfig;
    }

    /**
     * 是否是TCP
     * @return bool
     */
    public function isTcp(): bool
    {
        if ($this->isHttp()) return false;
        if ($this->isWebSocket()) return false;
        if ($this->getPortConfig()->getSockType() == PortConfig::SWOOLE_SOCK_TCP ||
            $this->getPortConfig()->getSockType() == PortConfig::SWOOLE_SOCK_TCP6) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 是否是HTTP
     * @return bool
     */
    public function isHttp(): bool
    {
        return $this->getPortConfig()->isOpenHttpProtocol() || $this->getPortConfig()->isOpenWebsocketProtocol();
    }

    /**
     * 是否是WebSocket
     * @return bool
     */
    public function isWebSocket(): bool
    {
        return $this->getPortConfig()->isOpenWebsocketProtocol();
    }

    /**
     * 是否是UDP
     * @return bool
     */
    public function isUDP(): bool
    {
        if ($this->getPortConfig()->getSockType() == PortConfig::SWOOLE_SOCK_UDP ||
            $this->getPortConfig()->getSockType() == PortConfig::SWOOLE_SOCK_UDP6) {
            return true;
        } else {
            return false;
        }
    }

    public function _onConnect($server, int $fd, int $reactorId)
    {
        //未准备好直接关闭连接
        if (!Server::$instance->getAbstractServer()->getProcessManager()->getCurrentProcess()->isReady()) {
            Server::$instance->getAbstractServer()->closeFd($fd);
            return;
        }
        try {
            $this->onTcpConnect($fd, $reactorId);
        } catch (Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    public abstract function onTcpConnect(int $fd, int $reactorId);

    public function _onClose($server, int $fd, int $reactorId)
    {
        //未准备好直接关闭连接
        if (!Server::$instance->getAbstractServer()->getProcessManager()->getCurrentProcess()->isReady()) {
            return;
        }
        try {
            $this->onTcpClose($fd, $reactorId);
        } catch (Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    public abstract function onTcpClose(int $fd, int $reactorId);

    public function _onReceive($server, int $fd, int $reactorId, string $data)
    {
        //未准备好直接关闭连接
        if (!Server::$instance->getAbstractServer()->getProcessManager()->getCurrentProcess()->isReady()) {
            Server::$instance->getAbstractServer()->closeFd($fd);
            return;
        }
        try {
            $this->onTcpReceive($fd, $reactorId, $data);
        } catch (Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    public abstract function onTcpReceive(int $fd, int $reactorId, string $data);

    /**
     * @param $server
     * @param string $data
     * @param array $clientInfo
     */
    public function _onPacket($server, string $data, array $clientInfo)
    {
        //未准备好直接关闭连接
        if (!Server::$instance->getAbstractServer()->getProcessManager()->getCurrentProcess()->isReady()) {
            return;
        }
        try {
            $this->onUdpPacket($data, $clientInfo);
        } catch (Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    public abstract function onUdpPacket(string $data, array $clientInfo);

    /**
     * @param $request
     * @param $response
     */
    public function _onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        //未准备好直接关闭连接
        if (!Server::$instance->getAbstractServer()->getProcessManager()->getCurrentProcess()->isReady()) {
            $response->end("server is not ready");
            return;
        }
        $_request = new \ESD\Server\Co\Beans\Request($request);
        $_response = new \ESD\Server\Co\Beans\Response($response);
        try {
            setContextValue("request", $_request, true);
            setContextValue("response", $_response, true);
            $this->onHttpRequest($_request, $_response);
        } catch (Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
        $_response->end("");
    }

    public abstract function onHttpRequest(Request $request, Response $response);

    /**
     * @param $server
     * @param $frame
     */
    public function _onMessage($server, $frame)
    {
        //未准备好直接关闭连接
        if (!Server::$instance->getAbstractServer()->getProcessManager()->getCurrentProcess()->isReady()) {
            return;
        }
        try {
            if (isset($frame->code)) {
                //是个CloseFrame
                $this->onWsMessage(new WebSocketCloseFrame($frame));
            } else {
                $this->onWsMessage(new WebSocketFrame($frame));
            }
        } catch (Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    public abstract function onWsMessage(WebSocketFrame $frame);

    /**
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     * @return bool
     * @throws Exception
     */
    public function _onHandshake($request, $response)
    {
        //未准备好直接关闭连接
        if (!Server::$instance->getAbstractServer()->getProcessManager()->getCurrentProcess()->isReady()) {
            return false;
        }
        $_request = new \ESD\CO\Server\Beans\Request($request);
        setContextValue("request", $_request, true);
        $success = $this->onWsPassCustomHandshake($_request);
        if (!$success) return false;
        // websocket握手连接算法验证
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->end();
            return false;
        }
        $key = base64_encode(sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        ));
        $headers = [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $key,
            'Sec-WebSocket-Version' => '13',
        ];
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }
        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }
        $response->status(101);
        $response->end();
        Server::$instance->getAbstractServer()->defer(function () use ($request) {
            $this->_onOpen(Server::$instance->getAbstractServer()->getServer(), $request);
        });
        return true;
    }

    public abstract function onWsPassCustomHandshake(Request $request): bool;

    /**
     * @param \Swoole\WebSocket\Server $server
     * @param $request
     */
    public function _onOpen($server, $request)
    {
        //未准备好直接关闭连接
        if (!Server::$instance->getAbstractServer()->getProcessManager()->getCurrentProcess()->isReady()) {
            $server->close($request->fd);
            return;
        }
        try {
            $_request = new \ESD\CO\Server\Beans\Request($request);
            setContextValue("request", $_request, true);
            $this->onWsOpen($_request);
        } catch (Throwable $e) {
            Server::$instance->getLog()->error($e);
        }
    }

    public abstract function onWsOpen(Request $request);

}