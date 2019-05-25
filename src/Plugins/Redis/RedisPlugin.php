<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 10:30
 */

namespace ESD\Plugins\Redis;


use ESD\Core\Logger\GetLogger;
use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Server\Server;

class RedisPlugin extends AbstractPlugin
{
    use GetLogger;
    /**
     * @var RedisConfig[]
     */
    protected $configList = [];
    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Redis";
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \ESD\Core\Config\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        ini_set('default_socket_timeout', '-1');
        //所有配置合併
        foreach ($this->configList as $config) {
            $config->merge();
        }
        $this->setToDIContainer(\Redis::class,new RedisProxy());
        return;
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @throws \ReflectionException
     * @throws RedisException
     */
    public function beforeProcessStart(Context $context)
    {
        ini_set('default_socket_timeout', '-1');
        $redisManyPool = new RedisManyPool();
        //重新获取配置
        $this->configList = [];
        $configs = Server::$instance->getConfigContext()->get(RedisConfig::key, []);
        if (empty($configs)) {
            $this->warn("没有redis配置");
        }
        foreach ($configs as $key => $value) {
            $redisConfig = new RedisConfig("");
            $redisConfig->setName($key);
            $this->configList[$key] = $redisConfig->buildFromConfig($value);
            $redisPool = new RedisPool($redisConfig);
            $redisManyPool->addPool($redisPool);
            $this->debug("已添加名为 {$redisConfig->getName()} 的Redis连接池");
        }
        $context->add("redisPool", $redisManyPool);
        $this->setToDIContainer(RedisManyPool::class,$redisManyPool);
        $this->setToDIContainer(RedisPool::class,$redisManyPool->getPool());
        $this->ready();
    }

    /**
     * @return RedisConfig[]
     */
    public function getConfigList(): array
    {
        return $this->configList;
    }

    /**
     * @param RedisConfig[] $configList
     */
    public function setConfigList(array $configList): void
    {
        $this->configList = $configList;
    }
}