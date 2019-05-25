<?php


namespace ESD\Server\Swoole\Channel;


use ESD\Core\DI\Factory;

class ChannelFactory implements Factory
{

    public function create($params)
    {
        return new SplChannel($params[0] ?? 1);
    }
}