<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 13:55
 */

namespace ESD\Core\Server\Port;

use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Core\Server\Beans\WebSocketFrame;
use ESD\Core\Server\Config\PortConfig;

interface IServerPort
{
    /**
     * @return PortConfig
     */
    public function getPortConfig();
}