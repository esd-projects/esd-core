<?php
namespace ESD\ExampleClass\Port;

use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Core\Server\Beans\WebSocketFrame;
use ESD\Core\Server\Port\ServerPort;

class SwoolePort extends SwoolePort__AopProxied implements \Go\Aop\Proxy
{

    /**
     * Property was created automatically, do not change it manually
     */
    private static $__joinPoints = [
        'method' => [
            'onHttpRequest' => [
                'advisor.ESD\\Plugins\\Pack\\Aspect\\PackAspect->aroundHttpRequest',
                'advisor.ESD\\Plugins\\EasyRoute\\Aspect\\RouteAspect->aroundHttpRequest'
            ],
            'onTcpReceive' => [
                'advisor.ESD\\Plugins\\EasyRoute\\Aspect\\RouteAspect->aroundTcpReceive'
            ],
            'onWsMessage' => [
                'advisor.ESD\\Plugins\\EasyRoute\\Aspect\\RouteAspect->aroundWsMessage'
            ],
            'onUdpPacket' => [
                'advisor.ESD\\Plugins\\EasyRoute\\Aspect\\RouteAspect->aroundUdpPacket'
            ]
        ]
    ];
    
    
    public function onHttpRequest(\ESD\Core\Server\Beans\Request $request, \ESD\Core\Server\Beans\Response $response)
    {
        return self::$__joinPoints['method:onHttpRequest']->__invoke($this, [$request, $response]);
    }
    
    
    public function onTcpReceive(int $fd, int $reactorId, string $data)
    {
        return self::$__joinPoints['method:onTcpReceive']->__invoke($this, [$fd, $reactorId, $data]);
    }
    
    
    public function onWsMessage(\ESD\Core\Server\Beans\WebSocketFrame $frame)
    {
        return self::$__joinPoints['method:onWsMessage']->__invoke($this, [$frame]);
    }
    
    
    public function onUdpPacket(string $data, array $clientInfo)
    {
        return self::$__joinPoints['method:onUdpPacket']->__invoke($this, [$data, $clientInfo]);
    }
    
}
\Go\Proxy\ClassProxy::injectJoinPoints(SwoolePort::class);