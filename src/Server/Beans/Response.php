<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 14:54
 */

namespace ESD\Core\Server\Beans;

/**
 * HTTP请求返回对象
 * Class Response
 */
abstract class Response
{
    /**
     * @var bool
     */
    protected $isEnd = false;

    protected $fd;

    protected $buffer = "";

    /**
     * swoole的原始对象
     * @var \Swoole\Http\Response
     */
    protected $swooleResponse;

    public function __construct()
    {

    }

    /**
     * 设置HTTP响应的Header信息。
     * @param string $key
     * @param string $value
     * @param bool $ucwords 是否需要对Key进行Http约定格式化，默认true会自动格式化
     */
    abstract public function addHeader(string $key, string $value, bool $ucwords = true);

    /**
     * 设置HTTP响应的cookie信息。此方法参数与PHP的setcookie完全一致。
     * 底层自动会对$value进行urlencode编码，可使用rawCookie关闭对$value的编码处理
     * 底层允许设置多个相同$key的COOKIE
     * @param string $key
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     */
    abstract public function addCookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false);

    /**
     * @param string $key
     */
    abstract public function delCookie(string $key);

    /**
     * 发送Http状态码。
     * @param int $http_status_code
     */
    abstract public function setStatus(int $http_status_code);

    /**
     * 发送Http跳转。调用此方法会自动end发送并结束响应。
     * @param string $url
     * @param int $http_code
     */
    abstract public function redirect(string $url, int $http_code = 302);

    /**
     * 启用Http Chunk分段向浏览器发送相应内容。关于Http Chunk可以参考Http协议标准文档。
     * 使用write分段发送数据后，end方法将不接受任何参数
     * 调用end方法后会发送一个长度为0的Chunk表示数据传输完毕
     * @param string $data
     */
    abstract public function write(string $data);

    /**
     * 发送文件到浏览器。
     * 调用sendfile前不得使用write方法发送Http-Chunk
     * 调用sendfile后底层会自动执行end
     * @param string $filename 要发送的文件名称，文件不存在或没有访问权限sendfile会失败
     * @param int $offset
     * @param int $length
     */
    abstract public function sendfile(string $filename, int $offset = 0, int $length = 0);

    /**
     * 像缓冲区增加数据
     * @param string|null $html
     */
    public function append(?string $html)
    {
        if (!empty($html)) {
            $this->buffer .= $html;
        }
    }

    /**
     * 清除缓冲区
     */
    public function clear()
    {
        $this->buffer = "";
    }

    /**
     * 发送Http响应体，并结束请求处理。
     * @param string $html
     */
    abstract public function end(?string $html);

    /**
     * 分离响应对象。使用此方法后，$response对象销毁时不会自动end，与Http\Response::create和Server::send配合使用。
     */
    abstract public function detach();


    /**
     * @return mixed
     */
    public function getFd()
    {
        return $this->fd;
    }

    /**
     * @return mixed
     */
    abstract public function getHeader();

    /**
     * @param mixed $header
     */
    abstract public function setHeader($header): void;

    /**
     * @return mixed
     */
    abstract public function getCookie();

    /**
     * @param mixed $cookie
     */
    abstract public function setCookie($cookie): void;

    /**
     * @return bool
     */
    public function isEnd(): bool
    {
        return $this->isEnd;
    }
}