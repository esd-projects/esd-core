<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 10:59
 */

namespace ESD\Plugins\Redis;
use Redis;

class RedisManyPool
{
    protected $poolList = [];

    /**
     * 获取连接池
     * @param $name
     * @return RedisPool|null
     */
    public function getPool($name = "default")
    {
        return $this->poolList[$name] ?? null;
    }

    /**
     * 添加连接池
     * @param RedisPool $redisPool
     */
    public function addPool(RedisPool $redisPool)
    {
        $this->poolList[$redisPool->getRedisConfig()->getName()] = $redisPool;
    }

    /**
     * @return Redis
     * @throws RedisException
     */
    public function db(): Redis
    {
        $default = $this->getPool();
        if ($default == null) {
            throw new RedisException("没有设置默认的redis");
        }
        return $this->getPool()->db();
    }
}