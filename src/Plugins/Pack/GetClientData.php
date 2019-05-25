<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/25
 * Time: 15:44
 */

namespace ESD\Plugins\Pack;


trait GetClientData
{
    public function getClientData(): ?ClientData
    {
       return getDeepContextValueByClassName(ClientData::class);
    }
}