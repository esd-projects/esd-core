<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/17
 * Time: 15:02
 */

namespace ESD\Core\Client;

use ESD\Core\Server\Config\PortConfig;
use Swoole;

class Client
{
    protected $swooleClient;

    public function __construct(int $sock_type = PortConfig::SWOOLE_SOCK_TCP)
    {
        $this - $this->swooleClient = new Coroutine\Client($sock_type);
    }

    /**
     * connect方法接受4个参数：
     * $host是远程服务器的地址，2.0.12或更高版本可直接传入域名，底层会自动进行协程切换解析域名为IP地址
     * $port是远程服务器端口
     * $timeout是网络IO的超时时间，包括connect/send/recv，单位是秒s，支持浮点数。默认为0.5s，即100ms，超时发生时，连接会被自动close掉
     * connect操作会有一次协程切换开销，connect发起时yield，完成时resume
     * @param string $host
     * @param int $port
     * @param float $timeout
     * @param int $sock_flag
     * @return bool
     */
    public function connect(string $host, int $port, float $timeout = 0.5, int $sock_flag = 0): bool
    {
        return $this->swooleClient->connect($host, $port, $timeout, $sock_flag);
    }

    /**
     * 发送成功返回写入Socket缓存区的字节数，底层会尽可能地将所有数据发出。
     * 如果返回的字节数与传入的$data长度不同，可能是Socket已被对端关闭，再下一次调用send或recv时将返回对应的错误码
     * @param string $data
     * @return mixed
     */
    public function send(string $data)
    {
        return $this->swooleClient->send($data);
    }

    /**
     * recv方法用于从服务器端接收数据。
     * 底层会自动yield，等待数据接收完成后自动切换到当前协程。
     * 设置了通信协议，recv会返回完整的数据，长度受限于package_max_length
     * 未设置通信协议，recv最大返回64K数据
     * 未设置通信协议返回原始的数据，需要PHP代码中自行实现网络协议的处理
     * recv返回空字符串表示服务端主动关闭连接，需要close
     * recv失败，返回false，检测$client->errCode获取错误原因
     * 传入了$timeout，优先使用制定的timeout参数
     * 未传入$timeout，但在connect时指定了超时时间，自动以connect超时时间作为recv超时时间
     * 未传入$timeout，未设置connect超时，将设置为-1表示永不超时
     * 发生超时的错误码为ETIMEDOUT
     * @param float $timeout
     * @return string
     */
    public function recv(float $timeout = -1): string
    {
        return $this->swooleClient->recv($timeout);
    }

    /**
     * 关闭连接。
     * close不存在阻塞，会立即返回。
     * 执行成功返回true，失败返回false
     * @return bool
     */
    public function close(): bool
    {
        return $this->swooleClient->close();
    }

    /**
     * 窥视数据。
     * peek方法直接操作socket，因此不会引起协程调度。
     * peek方法仅用于窥视内核socket缓存区中的数据，不进行偏移。使用peek之后，再调用recv仍然可以读取到这部分数据
     * peek方法是非阻塞的，它会立即返回。当socket缓存区中有数据时，会返回数据内容。缓存区为空时返回false，并设置$client->errCode
     * 连接已被关闭peek会返回空字符串
     * @param int $length
     * @return string
     */
    public function peek(int $length = 65535): string
    {
        return $this->swooleClient->peek($length);
    }

    /**
     * 设置客户端参数。
     * @param ClientConfig $clientConfig
     * @throws \ReflectionException
     */
    public function set(ClientConfig $clientConfig)
    {
        $this->swooleClient->set($clientConfig->toConfigArray());
    }

    /**
     * 获取服务器端证书信息。
     * 执行成功返回一个X509证书字符串信息
     * 执行失败返回false
     * 必须在SSL握手完成后才可以调用此方法
     * 可以使用openssl扩展提供的openssl_x509_parse函数解析证书的信息
     * @return mixed
     */
    public function getPeerCert()
    {
        return $this->swooleClient->getPeerCert();
    }

    /**
     * 返回swoole_client的连接状态
     * @return mixed
     */
    public function isConnected()
    {
        return $this->swooleClient->isConnected();
    }

    /**
     * 用于获取客户端socket的本地host:port，必须在连接之后才可以使用。
     * 调用成功返回一个数组，如：array('host' => '127.0.0.1', 'port' => 53652)
     * @return array
     */
    public function getSockName()
    {
        return $this->swooleClient->getSockName();
    }

    /**
     * 获取对端socket的IP地址和端口，仅支持SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6类型的swoole_client对象。
     * @return mixed
     */
    public function getPeerName()
    {
        return $this->swooleClient->getPeerName();
    }

    /**
     * 向任意IP:PORT的主机发送UDP数据包，仅支持SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6类型的swoole_client对象。
     * @param string $ip
     * @param int $port
     * @param string $data
     */
    public function sendTo(string $ip, int $port, string $data)
    {
        $this->swooleClient->sendto($ip, $port, $data);
    }

    /**
     * 发送文件到服务器，本函数是基于sendfile操作系统调用实现.
     * @param string $filename 指定要发送文件的路径
     * @param int $offset 上传文件的偏移量，可以指定从文件的中间部分开始传输数据。此特性可用于支持断点续传。
     * @param int $length 发送数据的尺寸，默认为整个文件的尺寸
     * @return bool 如果传入的文件不存在，将返回false 执行成功返回true
     */
    public function sendFile(string $filename, int $offset = 0, int $length = 0): bool
    {
        return $this->swooleClient->sendfile($filename, $offset, $length);
    }

    /**
     * 动态开启SSL隧道加密。客户端在建立连接时使用明文通信，中途希望改为SSL隧道加密通信，可以使用enableSSL方法来实现。使用enableSSL动态开启SSL隧道加密，需要满足两个条件：
     * 客户端创建时类型必须为非SSL
     * 客户端已与服务器建立了连接
     */
    public function enableSSL()
    {
        $this->swooleClient->enableSSL();
    }

    public function getErrCode(): int
    {
        return $this->swooleClient->errCode();
    }

    public function getErrStr(): string
    {
        return socket_strerror($this->swooleClient->errCode);
    }

    /**
     * sock属性是此socket的文件描述符
     * @return int
     */
    public function getSock(): int
    {
        return $this->swooleClient->sock;
    }

    /**
     * 表示此连接是新创建的还是复用已存在的。与SWOOLE_KEEP配合使用。
     * @return mixed
     */
    public function getReuse(): bool
    {
        return $this->swooleClient->reuse;
    }
}