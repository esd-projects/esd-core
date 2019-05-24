<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 14:53
 */

namespace ESD\Server\Co\Beans;

/**
 * HTTP请求对象
 * Class Request
 */
class Request extends \ESD\Core\Server\Beans\Request
{
    public function __construct($swooleRequest)
    {
        parent::__construct();

        $this->swooleRequest = $swooleRequest;

        $this->header = $this->swooleRequest->header;
        $this->server = $this->swooleRequest->server;
        $this->get = $this->swooleRequest->get;
        $this->post = $this->swooleRequest->post;
        $this->cookie = $this->swooleRequest->cookie;
        $this->files = $this->swooleRequest->files;
        $this->fd = $this->swooleRequest->fd;
        $this->streamId = $this->swooleRequest->streamId;
    }

    /**
     * @return mixed
     */
    public function getSwooleRequest()
    {
        return $this->swooleRequest;
    }

    /**
     * 获取原始的POST包体，用于非application/x-www-form-urlencoded格式的Http POST请求。
     * @return string
     */
    public function getRawContent(): string
    {
        return $this->swooleRequest->rawContent();
    }

    /**
     * 获取完整的原始Http请求报文。包括Http Header和Http Body
     * @return string
     */
    public function getData(): string
    {
        return $this->swooleRequest->getData();
    }
}