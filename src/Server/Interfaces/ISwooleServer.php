<?php


namespace ESD\Core\Server\Interfaces;


use ESD\Core\Exception\ConfigException;
use ESD\Core\Server\Beans\ClientInfo;
use ESD\Core\Server\Beans\ServerStats;
use ESD\Core\Server\Process\Process;
use Iterator;
use ReflectionException;

interface ISwooleServer
{

    /**
     * TCP连接迭代器
     * @return Iterator
     */
    public function getConnections(): Iterator;

    /**
     * 获取连接的信息
     * @param int $fd
     * @return ClientInfo
     */
    public function getClientInfo(int $fd): ClientInfo;

    /**
     * 关闭客户端连接
     * $reset设置为true会强制关闭连接，丢弃发送队列中的数据
     * @param int $fd
     * @param bool $reset
     */
    public function closeFd(int $fd, bool $reset = false);

    /**
     * 自动判断是ws还是tcp
     * @param int $fd
     * @param string $data
     */
    public function autoSend(int $fd, string $data);

    /**
     * 向客户端发送数据
     * @param int $fd 客户端的文件描述符
     * @param string $data 发送的数据
     * @param int $serverSocket 向Unix Socket DGRAM对端发送数据时需要此项参数，TCP客户端不需要填写
     * @return bool 发送成功会返回true
     */
    public function send(int $fd, string $data, int $serverSocket = -1): bool;

    /**
     * 发送文件到TCP客户端连接
     * @param int $fd
     * @param string $filename 要发送的文件路径，如果文件不存在会返回false
     * @param int $offset 指定文件偏移量，可以从文件的某个位置起发送数据。默认为0，表示从文件头部开始发送
     * @param int $length 指定发送的长度，默认为文件尺寸。
     * @return bool 操作成功返回true，失败返回false
     */
    public function sendFile(int $fd, string $filename, int $offset = 0, int $length = 0): bool;

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
    public function sendToUpd(string $ip, int $port, string $data, int $server_socket = -1): bool;

    /**
     * 检测fd对应的连接是否存在。
     * @param $fd
     * @return bool
     */
    public function existFd($fd): bool;

    /**
     * 将连接绑定一个用户定义的UID，可以设置dispatch_mode=5设置以此值进行hash固定分配。可以保证某一个UID的连接全部会分配到同一个Worker进程。
     * @param int $fd
     * @param int $uid
     */
    public function bindUid(int $fd, int $uid);

    /**
     * 得到当前Server的活动TCP连接数，启动时间，accpet/close的总次数等信息。
     * @return ServerStats
     */
    public function stats(): ServerStats;

    /**
     * 检测服务器所有连接，并找出已经超过约定时间的连接。如果指定if_close_connection，则自动关闭超时的连接。未指定仅返回连接的fd数组。
     * 调用成功将返回一个连续数组，元素是已关闭的$fd
     * 调用失败返回false
     * @param bool $if_close_connection
     * @return array
     */
    public function heartbeat(bool $if_close_connection = true): array;

    /**
     * 获取最近一次操作错误的错误码。业务代码中可以根据错误码类型执行不同的逻辑。
     * 1001 连接已经被Server端关闭了，出现这个错误一般是代码中已经执行了$serv->close()关闭了某个连接，但仍然调用$serv->send()向这个连接发送数据
     * 1002 连接已被Client端关闭了，Socket已关闭无法发送数据到对端
     * 1003 正在执行close，onClose回调函数中不得使用$serv->send()
     * 1004 连接已关闭
     * 1005 连接不存在，传入$fd 可能是错误的
     * 1007 接收到了超时的数据，TCP关闭连接后，可能会有部分数据残留在管道缓存区内，这部分数据会被丢弃
     * 1008 发送缓存区已满无法执行send操作，出现这个错误表示这个连接的对端无法及时收数据导致发送缓存区已塞满
     * 1202 发送的数据超过了 Server->buffer_output_size 设置
     * @return int
     */
    public function getLastError(): int;

    /**
     * 设置客户端连接为保护状态，不被心跳线程切断。
     * $value 设置的状态，true表示保护状态，false表示不保护
     * @param int $fd
     * @param bool $value
     */
    public function protect(int $fd, bool $value = true);

    /**
     * 确认连接，与enable_delay_receive配合使用。
     * 当客户端建立连接后，并不监听可读事件。
     * 仅触发onConnect事件回调，在onConnect回调中执行confirm确认连接，这时服务器才会监听可读事件，接收来自客户端连接的数据。
     * @param int $fd
     */
    public function confirm(int $fd);

    /**
     * 重启所有Worker/Task进程。
     */
    public function reload();

    /**
     * 关闭服务器
     */
    public function shutdown();

    /**
     * 延后执行一个PHP函数
     * @param callable $callback
     */
    public function defer(callable $callback);

    /**
     * 添加一个进程
     * @param string $name
     * @param null $processClass 不填写将用默认的
     * @param string $groupName
     * @throws ConfigException
     * @throws ReflectionException
     */
    public function addProcess(string $name, $processClass = null, string $groupName = Process::DEFAULT_GROUP);
}