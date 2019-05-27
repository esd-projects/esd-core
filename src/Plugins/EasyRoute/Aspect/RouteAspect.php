<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 14:54
 */

namespace ESD\Plugins\EasyRoute\Aspect;


use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\EasyRoute\Controller\IController;
use ESD\Plugins\EasyRoute\EasyRouteConfig;
use ESD\Plugins\EasyRoute\RouteConfig;
use ESD\Plugins\EasyRoute\RouteException;
use ESD\Plugins\EasyRoute\RouteTool\IRoute;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Pack\GetBoostSend;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;

class RouteAspect extends OrderAspect
{
    use GetLogger;
    use GetBoostSend;
    /**
     * @var EasyRouteConfig[]
     */
    protected $easyRouteConfigs;
    /**
     * @var IRoute[]
     */
    protected $routeTools = [];

    /**
     * @var IController[]
     */
    protected $controllers = [];

    /**
     * @var RouteConfig
     */
    protected $routeConfig;

    /**
     * RouteAspect constructor.
     * @param $easyRouteConfigs
     * @param $routeConfig
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct($easyRouteConfigs, RouteConfig $routeConfig)
    {
        $this->easyRouteConfigs = $easyRouteConfigs;
        foreach ($this->easyRouteConfigs as $easyRouteConfig) {
            if (!isset($this->routeTools[$easyRouteConfig->getRouteTool()])) {
                $className = $easyRouteConfig->getRouteTool();
                $this->routeTools[$easyRouteConfig->getRouteTool()] = DIget($className);
            }
        }
        $this->routeConfig = $routeConfig;
    }

    /**
     * around onHttpRequest
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onHttpRequest(*))")
     */
    protected function aroundHttpRequest(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("EasyRouteConfig", $easyRouteConfig);
        $clientData = getContextValueByClassName(ClientData::class);
        $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
        try {
            $result = $routeTool->handleClientData($clientData, $easyRouteConfig);
            if (!$result) return;
            $controllerInstance = $this->getController($routeTool->getControllerName());
            $result = $controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams());
            if (is_array($result) || is_object($result)) {
                $result = json_encode($result, JSON_UNESCAPED_UNICODE);
            }
            $clientData->getResponse()->append($result);
        } catch (\Throwable $e) {
            try {
                //这里的错误会移交给IndexController处理
                $controllerInstance = $this->getController($this->routeConfig->getErrorControllerName());
                $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                $clientData->getResponse()->append($controllerInstance->onExceptionHandle($e));
            } catch (\Throwable $e) {
                $this->warn($e);
            }
            throw $e;
        }
        return;
    }

    /**
     * around onTcpReceive
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onTcpReceive(*))")
     */
    protected function aroundTcpReceive(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("EasyRouteConfig", $easyRouteConfig);
        $clientData = getContextValueByClassName(ClientData::class);
        $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
        try {
            $result = $routeTool->handleClientData($clientData, $easyRouteConfig);
            if (!$result) return;
            $controllerInstance = $this->getController($routeTool->getControllerName());
            $result = $controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams());
            if ($result != null) {
                $this->autoBoostSend($clientData->getFd(), $result);
            }
        } catch (\Throwable $e) {
            try {
                //这里的错误会移交给IndexController处理
                $controllerInstance = $this->getController($this->routeConfig->getErrorControllerName());
                $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                $controllerInstance->onExceptionHandle($e);
            } catch (\Throwable $e) {
                $this->warn($e);
            }
            throw $e;
        }
        return;
    }

    /**
     * around onWsMessage
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onWsMessage(*))")
     */
    protected function aroundWsMessage(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("EasyRouteConfig", $easyRouteConfig);
        $clientData = getContextValueByClassName(ClientData::class);
        $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
        try {
            $result = $routeTool->handleClientData($clientData, $easyRouteConfig);
            if (!$result) return;
            $controllerInstance = $this->getController($routeTool->getControllerName());
            $result = $controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams());
            if ($result != null) {
                $this->autoBoostSend($clientData->getFd(), $result);
            }
        } catch (\Throwable $e) {
            try {
                //这里的错误会移交给IndexController处理
                $controllerInstance = $this->getController($this->routeConfig->getErrorControllerName());
                $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                $controllerInstance->onExceptionHandle($e);
            } catch (\Throwable $e) {
                $this->warn($e);
            }
            throw $e;
        }
        return;
    }

    /**
     * around onUdpPacket
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onUdpPacket(*))")
     * @throws \Throwable
     */
    protected function aroundUdpPacket(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
        $clientData = getContextValueByClassName(ClientData::class);
        setContextValue("EasyRouteConfig", $easyRouteConfig);
        $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
        try {
            $result = $routeTool->handleClientData($clientData, $easyRouteConfig);
            if (!$result) return;
            $controllerInstance = $this->getController($routeTool->getControllerName());
            $controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams());
        } catch (\Throwable $e) {
            try {
                //这里的错误会移交给IndexController处理
                $controllerInstance = $this->getController($this->routeConfig->getErrorControllerName());
                $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                $controllerInstance->onExceptionHandle($e);
            } catch (\Throwable $e) {
                $this->warn($e);
            }
            throw $e;
        }
        return;
    }

    /**
     * @param $controllerName
     * @return IController
     * @throws RouteException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function getController($controllerName)
    {
        if (!isset($this->controllers[$controllerName])) {
            if (class_exists($controllerName)) {
                $controller = DIget($controllerName);
                if ($controller instanceof IController) {
                    $this->controllers[$controllerName] = $controller;
                    return $controller;
                } else {
                    throw new RouteException("类{$controllerName}应该继承IController");
                }
            } else {
                throw new RouteException("没有找到类$controllerName");
            }
        } else {
            return $this->controllers[$controllerName];
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "RouteAspect";
    }
}