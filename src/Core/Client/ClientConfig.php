<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/17
 * Time: 15:12
 */

namespace ESD\Core\Client;


use ESD\Core\Plugins\Config\ToConfigArray;

class ClientConfig
{
    use ToConfigArray;
    /**
     * 总超时，包括连接、发送、接收所有超时
     * @var float
     */
    protected $timeout;

    /**
     * 连接超时
     * @var float
     */
    protected $connectTimeout;

    /**
     * 接收超时
     * @var float
     */
    protected $readTimeout;

    /**
     * 发送超时
     * @var float
     */
    protected $writeTimeout;

    /**
     * 验证服务器端证书
     * 启用后会验证证书和主机域名是否对应，如果为否将自动关闭连接
     * @var bool
     */
    protected $sslVerifyPeer;

    /**
     * 允许自签名证书。
     * @var bool
     */
    protected $sslAllowSelfSigned;

    /**
     * 设置服务器主机名称，与ssl_verify_peer配置或Client::verifyPeerCert配合使用。
     * @var string
     */
    protected $sslHostName;

    /**
     * 当设置ssl_verify_peer为true时， 用来验证远端证书所用到的CA证书。 本选项值为CA证书在本地文件系统的全路径及文件名。
     * @var string
     */
    protected $sslCafile;

    /**
     * 如果未设置ssl_cafile，或者ssl_cafile所指的文件不存在时， 会在ssl_capath所指定的目录搜索适用的证书。 该目录必须是已经经过哈希处理的证书目录。
     * @var string
     */
    protected $sslCapath;

    /**
     * 配置Http代理。
     * @var array
     */
    protected $httpProxyHost;

    /**
     * 打开EOF检测，此选项将检测客户端连接发来的数据，当数据包结尾是指定的字符串时才会投递给Worker进程。
     * 否则会一直拼接数据包，直到超过缓存区或者超时才会中止。当出错时底层会认为是恶意连接，丢弃数据并强制关闭连接。
     * @var bool
     */
    protected $openEofCheck;
    /**
     * 启用EOF自动分包。
     * 当设置open_eof_check后，底层检测数据是否以特定的字符串结尾来进行数据缓冲,但默认只截取收到数据的末尾部分做对比,这时候可能会产生多条数据合并在一个包内。
     * @var bool
     */
    protected $openEofSplit;
    /**
     * 与 open_eof_check 或者 open_eof_split 配合使用，设置EOF字符串。
     * @var string
     */
    protected $packageEof;
    /**
     * 打开包长检测特性。包长检测提供了固定包头+包体这种格式协议的解析。启用后，可以保证Worker进程onReceive每次都会收到一个完整的数据包。
     * @var bool
     */
    protected $openLengthCheck;
    /**
     * 长度值的类型，接受一个字符参数，与php的 pack 函数一致。目前Swoole支持10种类型：
     * c：有符号、1字节
     * C：无符号、1字节
     * s ：有符号、主机字节序、2字节
     * S：无符号、主机字节序、2字节
     * n：无符号、网络字节序、2字节
     * N：无符号、网络字节序、4字节
     * l：有符号、主机字节序、4字节（小写L）
     * L：无符号、主机字节序、4字节（大写L）
     * v：无符号、小端字节序、2字节
     * V：无符号、小端字节序、4字节
     * @var string
     */
    protected $packageLengthType;
    /**
     * 设置最大数据包尺寸，单位为字节
     * @var int
     */
    protected $packageMaxLength;
    /**
     * 从第几个字节开始计算长度，一般有2种情况：
     * length的值包含了整个包（包头+包体），package_body_offset 为0
     * 包头长度为N字节，length的值不包含包头，仅包含包体，package_body_offset设置为N
     * @var int
     */
    protected $packageBodyOffset;
    /**
     * length长度值在包头的第几个字节。
     * @var int
     */
    protected $packageLengthOffset;

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return float
     */
    public function getConnectTimeout(): float
    {
        return $this->connectTimeout;
    }

    /**
     * @param float $connectTimeout
     */
    public function setConnectTimeout(float $connectTimeout): void
    {
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * @return float
     */
    public function getReadTimeout(): float
    {
        return $this->readTimeout;
    }

    /**
     * @param float $readTimeout
     */
    public function setReadTimeout(float $readTimeout): void
    {
        $this->readTimeout = $readTimeout;
    }

    /**
     * @return float
     */
    public function getWriteTimeout(): float
    {
        return $this->writeTimeout;
    }

    /**
     * @param float $writeTimeout
     */
    public function setWriteTimeout(float $writeTimeout): void
    {
        $this->writeTimeout = $writeTimeout;
    }

    /**
     * @return bool
     */
    public function isSslVerifyPeer(): bool
    {
        return $this->sslVerifyPeer;
    }

    /**
     * @param bool $sslVerifyPeer
     */
    public function setSslVerifyPeer(bool $sslVerifyPeer): void
    {
        $this->sslVerifyPeer = $sslVerifyPeer;
    }

    /**
     * @return bool
     */
    public function isSslAllowSelfSigned(): bool
    {
        return $this->sslAllowSelfSigned;
    }

    /**
     * @param bool $sslAllowSelfSigned
     */
    public function setSslAllowSelfSigned(bool $sslAllowSelfSigned): void
    {
        $this->sslAllowSelfSigned = $sslAllowSelfSigned;
    }

    /**
     * @return string
     */
    public function getSslHostName(): string
    {
        return $this->sslHostName;
    }

    /**
     * @param string $sslHostName
     */
    public function setSslHostName(string $sslHostName): void
    {
        $this->sslHostName = $sslHostName;
    }

    /**
     * @return string
     */
    public function getSslCafile(): string
    {
        return $this->sslCafile;
    }

    /**
     * @param string $sslCafile
     */
    public function setSslCafile(string $sslCafile): void
    {
        $this->sslCafile = $sslCafile;
    }

    /**
     * @return string
     */
    public function getSslCapath(): string
    {
        return $this->sslCapath;
    }

    /**
     * @param string $sslCapath
     */
    public function setSslCapath(string $sslCapath): void
    {
        $this->sslCapath = $sslCapath;
    }

    /**
     * @return array
     */
    public function getHttpProxyHost(): array
    {
        return $this->httpProxyHost;
    }

    /**
     * @param array $httpProxyHost
     */
    public function setHttpProxyHost(array $httpProxyHost): void
    {
        $this->httpProxyHost = $httpProxyHost;
    }

    /**
     * @return bool
     */
    public function isOpenEofCheck(): bool
    {
        return $this->openEofCheck;
    }

    /**
     * @param bool $openEofCheck
     */
    public function setOpenEofCheck(bool $openEofCheck): void
    {
        $this->openEofCheck = $openEofCheck;
    }

    /**
     * @return bool
     */
    public function isOpenEofSplit(): bool
    {
        return $this->openEofSplit;
    }

    /**
     * @param bool $openEofSplit
     */
    public function setOpenEofSplit(bool $openEofSplit): void
    {
        $this->openEofSplit = $openEofSplit;
    }

    /**
     * @return string
     */
    public function getPackageEof(): string
    {
        return $this->packageEof;
    }

    /**
     * @param string $packageEof
     */
    public function setPackageEof(string $packageEof): void
    {
        $this->packageEof = $packageEof;
    }

    /**
     * @return bool
     */
    public function isOpenLengthCheck(): bool
    {
        return $this->openLengthCheck;
    }

    /**
     * @param bool $openLengthCheck
     */
    public function setOpenLengthCheck(bool $openLengthCheck): void
    {
        $this->openLengthCheck = $openLengthCheck;
    }

    /**
     * @return string
     */
    public function getPackageLengthType(): string
    {
        return $this->packageLengthType;
    }

    /**
     * @param string $packageLengthType
     */
    public function setPackageLengthType(string $packageLengthType): void
    {
        $this->packageLengthType = $packageLengthType;
    }

    /**
     * @return int
     */
    public function getPackageMaxLength(): int
    {
        return $this->packageMaxLength;
    }

    /**
     * @param int $packageMaxLength
     */
    public function setPackageMaxLength(int $packageMaxLength): void
    {
        $this->packageMaxLength = $packageMaxLength;
    }

    /**
     * @return int
     */
    public function getPackageBodyOffset(): int
    {
        return $this->packageBodyOffset;
    }

    /**
     * @param int $packageBodyOffset
     */
    public function setPackageBodyOffset(int $packageBodyOffset): void
    {
        $this->packageBodyOffset = $packageBodyOffset;
    }

    /**
     * @return int
     */
    public function getPackageLengthOffset(): int
    {
        return $this->packageLengthOffset;
    }

    /**
     * @param int $packageLengthOffset
     */
    public function setPackageLengthOffset(int $packageLengthOffset): void
    {
        $this->packageLengthOffset = $packageLengthOffset;
    }

}