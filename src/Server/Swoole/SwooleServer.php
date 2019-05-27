<?php


namespace ESD\Server\Swoole;


use ESD\Core\Channel\Channel;
use ESD\Core\DI\DI;
use ESD\Core\Plugins\Event\EventCall;
use ESD\Core\Server\Config\ServerConfig;
use ESD\Core\Server\Server;
use ESD\Server\Swoole\Channel\ChannelFactory;
use ESD\Server\Swoole\Event\EventCallFactory;

abstract class SwooleServer extends Server
{
    public function __construct(ServerConfig $serverConfig, string $defaultPortClass, string $defaultProcessClass)
    {
        DI::$definitions = [
            EventCall::class => new EventCallFactory(),
            Channel::class => new ChannelFactory(),
        ];
        parent::__construct($serverConfig, $defaultPortClass, $defaultProcessClass);
    }
}