<?php


namespace ESD\Server\Swoole;


use ESD\Core\Channel\Channel;
use ESD\Core\DI\DI;
use ESD\Core\Event\EventCall;
use ESD\Core\Server\Config\ServerConfig;
use ESD\Core\Server\Server;
use ESD\Server\Co\Logger\LoggerStarter;
use ESD\Server\Swoole\Channel\ChannelFactory;
use ESD\Server\Swoole\Event\EventCallFactory;
use Psr\Log\LoggerInterface;

abstract class SwooleServer extends Server
{
    public function __construct(ServerConfig $serverConfig, string $defaultPortClass, string $defaultProcessClass)
    {
        DI::$definitions = [
            EventCall::class => new EventCallFactory(),
            Channel::class => new ChannelFactory(),
            LoggerInterface::class => function () {
                $loggerStarter = new LoggerStarter();
                return $loggerStarter->getLogger();
            }
        ];
        parent::__construct($serverConfig, $defaultPortClass, $defaultProcessClass);
    }
}