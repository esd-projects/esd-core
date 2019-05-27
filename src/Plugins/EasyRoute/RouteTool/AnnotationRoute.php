<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午3:11
 */

namespace ESD\Plugins\EasyRoute\RouteTool;


use ESD\Core\Exception;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\EasyRoute\Annotation\ModelAttribute;
use ESD\Plugins\EasyRoute\Annotation\PathVariable;
use ESD\Plugins\EasyRoute\Annotation\RequestBody;
use ESD\Plugins\EasyRoute\Annotation\RequestFormData;
use ESD\Plugins\EasyRoute\Annotation\RequestParam;
use ESD\Plugins\EasyRoute\Annotation\RequestRaw;
use ESD\Plugins\EasyRoute\Annotation\RequestRawJson;
use ESD\Plugins\EasyRoute\Annotation\RequestRawXml;
use ESD\Plugins\EasyRoute\Annotation\ResponseBody;
use ESD\Plugins\EasyRoute\EasyRouteConfig;
use ESD\Plugins\EasyRoute\EasyRoutePlugin;
use ESD\Plugins\EasyRoute\RouteException;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Validate\Annotation\ValidatedFilter;
use FastRoute\Dispatcher;

class AnnotationRoute implements IRoute
{
    use GetLogger;
    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * 设置反序列化后的数据 Object
     * @param ClientData $data
     * @param EasyRouteConfig $easyRouteConfig
     * @return bool
     * @throws Exception
     * @throws RouteException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ReflectionException
     */
    public function handleClientData(ClientData $data, EasyRouteConfig $easyRouteConfig): bool
    {
        $this->clientData = $data;
        $port = $this->clientData->getClientInfo()->getServerPort();
        $routeInfo = EasyRoutePlugin::$instance->getDispatcher()->dispatch($port . ":" . $this->clientData->getRequestMethod(), $this->clientData->getPath());
        $request = $this->clientData->getRequest();
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new RouteException("{$this->clientData->getPath()} 404 Not found");
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new RouteException("Method not allowed");
            case Dispatcher::FOUND: // 找到对应的方法
                $handler = $routeInfo[1]; // 获得处理函数
                $vars = $routeInfo[2]; // 获取请求参数
                $this->clientData->setControllerName($handler[0]->name);
                $this->clientData->setMethodName($handler[1]->name);
                $params = [];
                $methodReflection = $handler[1]->getReflectionMethod();
                foreach (EasyRoutePlugin::$instance->getScanClass()->getCachedReader()->getMethodAnnotations($methodReflection) as $annotation) {
                    if ($annotation instanceof ResponseBody) {
                        $data->getResponse()->addHeader("Content-Type", $annotation->value);
                    }
                    if ($annotation instanceof PathVariable) {
                        $result = $vars[$annotation->value] ?? null;
                        if ($annotation->required) {
                            if ($result == null) throw new RouteException("path {$annotation->value} not find");
                        }
                        $params[$annotation->param ?? $annotation->value] = $result;
                    } else if ($annotation instanceof RequestParam) {
                        if ($request == null) continue;
                        if ($annotation->required) {
                            $result = $request->getGetRequire($annotation->value);
                        } else {
                            $result = $request->getGet($annotation->value);
                        }
                        $params[$annotation->param ?? $annotation->value] = $result;
                    } else if ($annotation instanceof RequestFormData) {
                        if ($request == null) continue;
                        if ($annotation->required) {
                            $result = $request->getPostRequire($annotation->value);
                        } else {
                            $result = $request->getPost($annotation->value);
                        }
                        $params[$annotation->param ?? $annotation->value] = $result;
                    } else if ($annotation instanceof RequestRawJson || $annotation instanceof RequestBody) {
                        if ($request == null) continue;
                        if (!$json = json_decode($request->getRawContent(), true)) {
                            $this->warning('RequestRawJson errror, raw:' . $request->getRawContent());
                            throw new RouteException('RawJson Format error');
                        }
                        $params[$annotation->value] = $json;
                    } else if ($annotation instanceof ModelAttribute) {
                        if ($request == null) continue;
                        $params[$annotation->value] = $request->post();
                    } else if ($annotation instanceof RequestRaw) {
                        if ($request == null) continue;
                        $raw = $request->getRawContent();
                        $params[$annotation->value] = $raw;
                    } else if ($annotation instanceof RequestRawXml) {
                        if ($request == null) continue;
                        $raw = $request->getRawContent();
                        if (!$xml = simplexml_load_string($raw, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS)) {
                            $this->warning('RequestRawXml errror, raw:' . $request->getRawContent());
                            throw new RouteException('RawXml Format error');
                        }
                        $params[$annotation->value] = json_decode(json_encode($xml), true);
                    }
                }
                $realParams = [];
                if ($methodReflection instanceof \ReflectionMethod) {
                    foreach ($methodReflection->getParameters() as $parameter) {
                        if ($parameter->getClass() != null) {
                            $values = $params[$parameter->name];
                            if ($values != null) {
                                //验证
                                $values = ValidatedFilter::valid($parameter->getClass(), $values);
                                $instance = $parameter->getClass()->newInstance();
                                foreach ($instance as $key => $value) {
                                    $instance->$key = $values[$key] ?? null;
                                }
                                $realParams[$parameter->getPosition()] = $instance;
                            } else {
                                $realParams[$parameter->getPosition()] = null;
                            }
                        } else {
                            $realParams[$parameter->getPosition()] = $params[$parameter->name];
                        }
                    }
                }
                if (!empty($realParams)) {
                    $this->clientData->setParams($realParams);
                }
                break;
        }
        return true;
    }

    /**
     * 获取控制器名称
     * @return string
     */
    public function getControllerName()
    {
        if ($this->clientData == null) return null;
        return $this->clientData->getControllerName();
    }

    /**
     * 获取方法名称
     * @return string
     */
    public function getMethodName()
    {
        if ($this->clientData == null) return null;
        return $this->clientData->getMethodName();
    }

    public function getPath()
    {
        if ($this->clientData == null) return null;
        return $this->clientData->getPath();
    }

    public function getParams()
    {
        if ($this->clientData == null) return null;
        return $this->clientData->getParams();
    }

    /**
     * @return ClientData
     */
    public function getClientData(): ?ClientData
    {
        return $this->clientData;
    }
}