<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 14:42
 */

namespace ESD\Plugins\EasyRoute;


use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Core\Server\Server;
use ESD\Plugins\AnnotationsScan\AnnotationsScanPlugin;
use ESD\Plugins\AnnotationsScan\ScanClass;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\EasyRoute\Annotation\Controller;
use ESD\Plugins\EasyRoute\Annotation\RequestMapping;
use ESD\Plugins\EasyRoute\Aspect\RouteAspect;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Pack\ClientDataProxy;
use ESD\Plugins\Pack\PackPlugin;
use ESD\Plugins\Validate\ValidatePlugin;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use ReflectionClass;
use ReflectionMethod;
use function FastRoute\simpleDispatcher;

class EasyRoutePlugin extends AbstractPlugin
{
    public static $instance;
    /**
     * @var EasyRouteConfig[]
     */
    private $easyRouteConfigs = [];

    /**
     * @var RouteConfig
     */
    private $routeConfig;
    /**
     * @var RouteAspect
     */
    private $routeAspect;
    /**
     * @var Dispatcher
     */
    private $dispatcher;
    /**
     * @var ScanClass
     */
    private $scanClass;

    /**
     * EasyRoutePlugin constructor.
     * @param RouteConfig|null $routeConfig
     * @throws \DI\DependencyException
     * @throws \ReflectionException
     */
    public function __construct(?RouteConfig $routeConfig = null)
    {
        parent::__construct();
        if ($routeConfig == null) {
            $routeConfig = new RouteConfig();
        }
        $this->routeConfig = $routeConfig;
        //需要插件支持
        $this->atAfter(AnnotationsScanPlugin::class);
        $this->atAfter(ValidatePlugin::class);
        $this->atAfter(PackPlugin::class);
        self::$instance = $this;
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "EasyRoute";
    }

    /**
     * @param Context $context
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ESD\Core\Exception
     * @throws \ReflectionException
     */
    public function init(Context $context)
    {
        parent::init($context);
        $configs = Server::$instance->getConfigContext()->get(PortConfig::key);
        foreach ($configs as $key => $value) {
            $easyRouteConfig = new EasyRouteConfig();
            $easyRouteConfig->setName($key);
            $easyRouteConfig->buildFromConfig($value);
            $easyRouteConfig->merge();
            $this->easyRouteConfigs[$easyRouteConfig->getPort()] = $easyRouteConfig;
        }
        $this->routeConfig->merge();
        $serverConfig = Server::$instance->getServerConfig();
        $aopConfig = Server::$instance->getContainer()->get(AopConfig::class);
        $aopConfig->addIncludePath($serverConfig->getVendorDir() . "/esd/base-server");
        $this->routeAspect = new RouteAspect($this->easyRouteConfigs, $this->routeConfig);
        $aopConfig->addAspect($this->routeAspect);
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \ESD\Core\Exception
     * @throws \ReflectionException
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlug(new AnnotationsScanPlugin());
        $pluginInterfaceManager->addPlug(new ValidatePlugin());
        $pluginInterfaceManager->addPlug(new PackPlugin());
    }

    /**
     * @param RouteRoleConfig $routeRole
     * @param RouteCollector $r
     * @param $reflectionClass
     * @param $reflectionMethod
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    protected function addRoute(RouteRoleConfig $routeRole, RouteCollector $r, $reflectionClass, $reflectionMethod)
    {
        $couldPortNames = [];
        if (!empty($routeRole->getPortTypes())) {
            foreach ($routeRole->getPortTypes() as $portType) {
                foreach ($this->easyRouteConfigs as $easyRouteConfig) {
                    if ($easyRouteConfig->getBaseType() == $portType) {
                        $couldPortNames[] = $easyRouteConfig->getName();
                    }
                }
            }
        } else {
            foreach ($this->easyRouteConfigs as $easyRouteConfig) {
                $couldPortNames[] = $easyRouteConfig->getName();
            }
        }
        //取并集
        if (!empty($routeRole->getPortNames())) {
            $couldPortNames = array_intersect($couldPortNames, $routeRole->getPortNames());
        }
        foreach ($couldPortNames as $portName) {
            $type = strtoupper($routeRole->getType());
            $port = Server::$instance->getPortManager()->getPortConfigs()[$portName]->getPort();
            Server::$instance->getLog()->info("Mapping $port:{$type} {$routeRole->getRoute()} to $reflectionClass->name::$reflectionMethod->name");
            $r->addRoute("$port:{$type}", $routeRole->getRoute(), [$reflectionClass, $reflectionMethod]);
        }
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        $this->routeConfig->merge();
        $this->setToDIContainer(ClientData::class, new ClientDataProxy());
        $this->scanClass = Server::$instance->getContainer()->get(ScanClass::class);
        $reflectionMethods = $this->scanClass->findMethodsByAnn(RequestMapping::class);

        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) use ($reflectionMethods) {
            //添加配置里的
            foreach ($this->routeConfig->getRouteRoles() as $routeRole) {
                $reflectionClass = new ReflectionClass($routeRole->getController());
                $reflectionMethod = new ReflectionMethod($routeRole->getController(), $routeRole->getMethod());
                $reflectionMethod->reflectionClass = $reflectionClass;
                $this->addRoute($routeRole, $r, $reflectionClass, $reflectionMethod);
            }
            //添加注解里的
            foreach ($reflectionMethods as $reflectionMethod) {
                $reflectionClass = $reflectionMethod->reflectionClass;
                $route = "/";
                $controller = $this->scanClass->getCachedReader()->getClassAnnotation($reflectionClass, Controller::class);
                if ($controller instanceof Controller) {
                    $controller->value = trim($controller->value, "/");
                    $route .= $controller->value;
                }
                $requestMapping = $this->scanClass->getCachedReader()->getMethodAnnotation($reflectionMethod, RequestMapping::class);
                if ($requestMapping instanceof RequestMapping) {
                    if (empty($requestMapping->value)) {
                        $requestMapping->value = $reflectionMethod->getName();
                    }
                    $requestMapping->value = trim($requestMapping->value, "/");
                    if (empty($controller->value)) {
                        $route .= $requestMapping->value;
                    } else {
                        $route .= "/" . $requestMapping->value;
                    }

                    if (empty($requestMapping->method)) {
                        $requestMapping->method[] = $controller->defaultMethod;
                    }
                    foreach ($requestMapping->method as $method) {
                        $routeRole = new RouteRoleConfig();
                        $routeRole->setRoute($route);
                        $routeRole->setType($method);
                        $routeRole->setController($reflectionClass->getName());
                        $routeRole->setMethod($reflectionMethod->getName());
                        $routeRole->setPortNames($controller->portNames);
                        $routeRole->setPortTypes($controller->portTypes);
                        $routeRole->buildName();
                        $this->routeConfig->addRouteRole($routeRole);
                        $this->addRoute($routeRole, $r, $reflectionClass, $reflectionMethod);
                    }
                }
            }
        });
        $this->routeConfig->merge();
    }

    /**
     * 在进程启动前
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }

    /**
     * @return RouteAspect
     */
    public function getRouteAspect(): RouteAspect
    {
        return $this->routeAspect;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @return ScanClass
     */
    public function getScanClass(): ScanClass
    {
        return $this->scanClass;
    }
}