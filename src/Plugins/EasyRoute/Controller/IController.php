<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 15:12
 */

namespace ESD\Plugins\EasyRoute\Controller;

interface IController
{
    public function handle(?string $controllerName, ?string $methodName, ?array $params);

    public function initialization(?string $controllerName, ?string $methodName);

    public function onExceptionHandle(\Throwable $e);
}