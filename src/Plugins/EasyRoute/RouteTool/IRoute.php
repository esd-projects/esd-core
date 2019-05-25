<?php

namespace ESD\Plugins\EasyRoute\RouteTool;

use ESD\Plugins\EasyRoute\EasyRouteConfig;
use ESD\Plugins\Pack\ClientData;

/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午3:09
 */
interface IRoute
{
    public function handleClientData(ClientData $data, EasyRouteConfig $easyRouteConfig): bool;

    public function getControllerName();

    public function getMethodName();

    public function getParams();

    public function getPath();
}