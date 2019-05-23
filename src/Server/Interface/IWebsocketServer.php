<?php


namespace ESD\Core\Server;


use ESD\Core\Server\Beans\WebSocketFrame;

interface IWebsocketServer
{
    /**
     * 向websocket客户端连接推送数据，长度最大不得超过2M。
     * @param int $fd
     * @param $data
     * @param int $opcode
     * @param bool $finish
     * @return bool
     */
    public function wsPush(int $fd, $data, int $opcode = 1, bool $finish = true): bool;

    /**
     * 主动向websocket客户端发送关闭帧并关闭该连接
     * @param int $fd
     * @param int $code 关闭连接的状态码，根据RFC6455，对于应用程序关闭连接状态码，取值范围为1000或4000-4999之间
     * @param string $reason 关闭连接的原因，utf-8格式字符串，字节长度不超过125
     * @return bool
     */
    public function wsDisconnect(int $fd, int $code = 1000, string $reason = ""): bool;

    /**
     * 检查连接是否为有效的WebSocket客户端连接。
     * 此函数与exist方法不同，exist方法仅判断是否为TCP连接，无法判断是否为已完成握手的WebSocket客户端。
     * @param int $fd
     * @return bool
     */
    public function isEstablished(int $fd): bool;

    /**
     * 打包WebSocket消息
     * 返回打包好的WebSocket数据包，可通过Socket发送给对端
     * @param WebSocketFrame $webSocketFrame 消息内容
     * @param bool $mask 是否设置掩码
     * @return string
     */
    public function wsPack(WebSocketFrame $webSocketFrame, $mask = false): string;

    /**
     * 解析WebSocket数据帧
     * 解析失败返回false
     * @param string $data
     * @return WebSocketFrame
     */
    public function wsUnPack(string $data): WebSocketFrame;
}