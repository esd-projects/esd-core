<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 11:08
 */

namespace ESD\Plugins\Cache\Aspect;

use DI\DependencyException;
use DI\NotFoundException;
use ESD\Coroutine\Co;
use ESD\Core\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Cache\Annotation\Cacheable;
use ESD\Plugins\Cache\Annotation\CacheEvict;
use ESD\Plugins\Cache\Annotation\CachePut;
use ESD\Plugins\Cache\CacheConfig;
use ESD\Plugins\Cache\CacheStorage;
use ESD\Plugins\Redis\GetRedis;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;

/**
 * Caching aspect
 */
class CachingAspect extends OrderAspect
{
    use GetLogger;
    use GetRedis;
    /**
     * @var CacheStorage
     */
    private $cacheStorage;


    /**
     * @var CacheConfig
     */
    private $config;

    /**
     * CachingAspect constructor.
     * @param CacheStorage $cacheStorage
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(CacheStorage $cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;
        $this->config = Server::$instance->getContainer()->get(CacheConfig::class);
    }


    /**
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @Around("@execution(ESD\Plugins\Cache\Annotation\Cacheable)")
     * @return mixed
     */
    public function aroundCacheable(MethodInvocation $invocation)
    {
        $cacheable = $invocation->getMethod()->getAnnotation(Cacheable::class);
        //初始化计算环境
        $p = $invocation->getArguments();
        //计算key
        $key = eval("return (" . $cacheable->key . ");");
        if (empty($key)) {
            $this->warn("cache key is empty,ignore this cache");
            //执行
            return $invocation->proceed();
        } else {
            $this->debug("cache get namespace:{$cacheable->namespace} key:{$key}");
            //计算condition
            $condition = true;
            if (!empty($cacheable->condition)) {
                $condition = eval("return (" . $cacheable->condition . ");");
            }
            $data = null;
            $data = $this->getCache($key, $cacheable);
            //获取到缓存就返回
            if ($data != null) {
                $this->debug("cache Hit!");
                return serverUnSerialize($data);
            }
            if ($condition) {
                if ($this->config->getLockTimeout() > 0) {
                    if ($this->config->getLockAlive() < $this->config->getLockTimeout()) {
                        $this->alert('cache 缓存配置项 lockAlive 必须大于 lockTimeout, 请立即修正参数');
                    }

                    if ($token = $this->cacheStorage->lock($key, $this->config->getLockAlive())) {
                        $result = $invocation->proceed();
                        $data = serverSerialize($result);
                        $this->setCache($key, $data, $cacheable);
                        $this->cacheStorage->unlock($key, $token);

                    } else {
                        $i = 0;
                        do {
                            $result = $this->getCache($key, $cacheable);
                            if ($result) break;
                            Co::sleep($this->config->getLockWait() / 1000.0);
                            $i += $this->config->getLockWait();
                            if ($i >= $this->config->getLockTimeout()) {
                                $result = $invocation->proceed();
                                $this->warn('lock wait timeout ' . $key . ',' . $i);
                                break;
                            } else {
                                $this->debug('lock wait ' . $key . ',' . $i);
                            }
                        } while ($i <= $this->config->getLockTimeout());
                    }
                } else {
                    $result = $invocation->proceed();
                    $data = serverSerialize($result);
                    $this->setCache($key, $data, $cacheable);
                }
            } else {
                $result = $invocation->proceed();
            }
            return $result;
        }
    }


    /**
     * This advice intercepts an execution of cachePut methods
     *
     * Logic is pretty simple: we look for the value in the cache and if it's not present here
     * then invoke original method and store it's result in the cache.
     *
     * Real-life examples will use APC or Memcache to store value in the cache
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @Around("@execution(ESD\Plugins\Cache\Annotation\CachePut)")
     * @return mixed
     */
    public function aroundCachePut(MethodInvocation $invocation)
    {
        $cachePut = $invocation->getMethod()->getAnnotation(CachePut::class);
        //初始化计算环境
        $p = $invocation->getArguments();
        //计算key
        $key = eval("return (" . $cachePut->key . ");");
        if (empty($key)) {
            $this->warn("cache key is empty,ignore this cache");
            //执行
            $result = $invocation->proceed();
        } else {
            $this->debug("cache put namespace:{$cachePut->namespace} key:{$key}");

            //计算condition
            $condition = true;
            if (!empty($cachePut->condition)) {
                $condition = eval("return (" . $cachePut->condition . ");");
            }
            //执行
            $result = $invocation->proceed();
            //可以缓存就缓存
            if ($condition) {
                $data = serverSerialize($result);
                if (empty($cachePut->namespace)) {
                    $this->cacheStorage->set($key, $data, $cachePut->time);
                } else {
                    $this->cacheStorage->setFromNameSpace($cachePut->namespace, $key, $data);
                }
            }
        }
        return $result;
    }

    /**
     * This advice intercepts an execution of cacheEvict methods
     *
     * Logic is pretty simple: we look for the value in the cache and if it's not present here
     * then invoke original method and store it's result in the cache.
     *
     * Real-life examples will use APC or Memcache to store value in the cache
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @Around("@execution(ESD\Plugins\Cache\Annotation\CacheEvict)")
     * @return mixed
     */
    public function aroundCacheEvict(MethodInvocation $invocation)
    {
        $cacheEvict = $invocation->getMethod()->getAnnotation(CacheEvict::class);
        //初始化计算环境
        $p = $invocation->getArguments();
        //计算key
        $key = eval("return (" . $cacheEvict->key . ");");
        if (empty($key)) {
            $this->warn("cache key is empty,ignore this cache");
            //执行
            $result = $invocation->proceed();
        } else {
            $this->debug("cache evict namespace:{$cacheEvict->namespace} key:{$key}");
            $result = null;
            if ($cacheEvict->beforeInvocation) {
                //执行
                $result = $invocation->proceed();
            }
            if (empty($cacheEvict->namespace)) {
                $this->cacheStorage->remove($key);
            } else {
                if ($cacheEvict->allEntries) {
                    $this->cacheStorage->removeNameSpace($cacheEvict->namespace);
                } else {
                    $this->cacheStorage->removeFromNameSpace($cacheEvict->namespace, $key);
                }
            }
            if (!$cacheEvict->beforeInvocation) {
                //执行
                $result = $invocation->proceed();
            }
        }
        return $result;
    }

    public function getCache($key, Cacheable $cacheable)
    {
        if (empty($cacheable->namespace)) {
            $data = $this->cacheStorage->get($key);
        } else {
            $data = $this->cacheStorage->getFromNameSpace($cacheable->namespace, $key);
        }
        return $data;
    }

    public function setCache($key, $data, Cacheable $cacheable): void
    {

        if (empty($cacheable->namespace)) {
            $ret = $this->cacheStorage->set($key, $data, $cacheable->time);
        } else {
            $ret = $this->cacheStorage->setFromNameSpace($cacheable->namespace, $key, $data);
        }

        if (!$ret) {
            $this->warn('cache key:' . $key . ' set fail ');
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "CachingAspect";
    }
}
