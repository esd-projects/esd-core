<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 13:55
 */

namespace ESD\Core\Server\Port;


use ESD\Core\Server\Beans\AbstractRequest;
use ESD\Core\Server\Beans\AbstractResponse;
use ESD\Core\Server\Beans\WebSocketFrame;

interface IServerPort
{
    public function onTcpConnect(int $fd, int $reactorId);

    public function onTcpClose(int $fd, int $reactorId);

    public function onTcpReceive(int $fd, int $reactorId, string $data);

    public function onUdpPacket(string $data, array $client_info);

    public function onHttpRequest(AbstractRequest $request, AbstractResponse $response);

    public function onWsMessage(WebSocketFrame $frame);

    public function onWsOpen(AbstractRequest $request);

    public function onWsPassCustomHandshake(AbstractRequest $request): bool;
}