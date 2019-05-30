<?php


namespace ESD\Core\Server\Beans;

use ESD\Core\Server\Beans\Http\Cookie;
use ESD\Core\Server\Beans\Http\MessageTrait;

abstract class AbstractResponse implements \Psr\Http\Message\ResponseInterface
{
    use MessageTrait;

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $cookies = [];

    /**
     * @var bool
     */
    protected $isEnd = false;

    /**
     * @var int
     */
    protected $fd;

    /**
     * Retrieve attributes derived from the request.
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name    The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     * @param string $name  The attribute name.
     * @param mixed  $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /**
     * Gets the response status code.
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int    $code         The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        $clone = clone $this;
        $clone->statusCode = (int)$code;
        return $clone;
    }

    public function getReasonPhrase()
    {
        return '';
    }

    /**
     * Return an instance with the specified charset content type.
     *
     * @param $charset
     * @return static
     * @throws \InvalidArgumentException
     */
    public function withCharset($charset): self
    {
        return $this->withAddedHeader('Content-Type', sprintf('charset=%s', $charset));
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return static
     */
    public function setCharset(string $charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Return an instance with specified cookies.
     *
     * @param Cookie $cookie
     * @return static
     */
    public function withCookie(Cookie $cookie)
    {
        $clone = clone $this;
        $clone->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        return $clone;
    }

    public function appendBody(string $body)
    {
        $clone = clone $this;
        $clone->stream->write($body);
        return $clone;
    }

    /**
     * 创建一个新对象，配合detach使用
     * @param $fd
     * @return static
     */
    abstract public static function create($fd);

    /**
     * 加载
     * @param null $realObject
     * @return mixed
     */
    abstract public function load($realObject = null);

    /**
     * 发送数据
     * @return mixed
     */
    abstract public function end();

    /**
     * 分离响应对象。使用此方法后，$response对象销毁时不会自动end，与Http\Response::create和Server::send配合使用。
     */
    abstract public function detach();

    /**
     * 是否已经发送
     * @return bool
     */
    abstract public function isEnd();

    /**
     * 发送Http跳转。调用此方法会自动end发送并结束响应。
     * @param string $url
     * @param int $http_code
     */
    abstract public function redirect(string $url, int $http_code = 302);
}