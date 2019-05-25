<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/28
 * Time: 17:55
 */

namespace ESD\Plugins\Redis;


trait GetRedis
{
    /**
     * @param string $name
     * @return mixed|\Redis
     * @throws RedisException
     */
    public function redis($name = "default")
    {
        $db = getContextValue("Redis:$name");
        if ($db == null) {
            $redisPool = getDeepContextValueByClassName(RedisManyPool::class);
            if ($redisPool instanceof RedisManyPool) {
                $db = $redisPool->getPool($name)->db();
                return $db;
            } else {
                throw new RedisException("没有找到名为{$name}的redis连接池");
            }
        } else {
            return $db;
        }
    }
}