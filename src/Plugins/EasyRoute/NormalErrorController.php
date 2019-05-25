<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/16
 * Time: 13:02
 */

namespace ESD\Plugins\EasyRoute;


use ESD\Plugins\EasyRoute\Controller\EasyController;

class NormalErrorController extends EasyController
{

    /**
     * 找不到方法时调用
     * @param $methodName
     * @return mixed
     * @throws RouteException
     */
    protected function defaultMethod(?string $methodName)
    {
        throw new RouteException("404 method $methodName can not find");
    }
}