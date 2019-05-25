<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 18:25
 */

namespace ESD\Plugins\Pack;


use ESD\Core\Server\Server;
use ESD\Plugins\Pack\Aspect\PackAspect;

trait GetBoostSend
{
    protected $packAspect;

    protected function getPackAspect(): PackAspect
    {
        if ($this->packAspect == null) {
            $packPlugin = Server::$instance->getPlugManager()->getPlug(PackPlugin::class);
            if ($packPlugin instanceof PackPlugin) {
                $this->packAspect = $packPlugin->getPackAspect();
            }
        }
        return $this->packAspect;
    }

    /**
     * 增强send，可以根据不同协议转码发送
     * @param $fd
     * @param $data
     * @param $topic
     * @return bool
     */
    public function autoBoostSend($fd, $data, $topic = null): bool
    {
        $this->getPackAspect()->autoBoostSend($fd, $data, $topic);
        return false;
    }
}