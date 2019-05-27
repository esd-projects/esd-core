<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/7
 * Time: 17:09
 */

namespace ESD\Plugins\Cache;


use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Redis\GetRedis;

class RedisCacheStorage implements CacheStorage
{
    use GetRedis;
    use GetLogger;
    /**
     * @var CacheConfig
     */
    private $cacheConfig;

    const prefix = "CACHE_";

    public function __construct(CacheConfig $cacheConfig)
    {
        $this->cacheConfig = $cacheConfig;
    }

    public function getFromNameSpace(string $nameSpace, string $id)
    {
        return $this->redis($this->cacheConfig->getDb())->hGet(self::prefix . $nameSpace, $id);
    }

    public function setFromNameSpace(string $nameSpace, string $id, string $data)
    {
        return $this->redis($this->cacheConfig->getDb())->hSet(self::prefix . $nameSpace, $id, $data);
    }

    public function removeFromNameSpace(string $nameSpace, string $id)
    {
        return $this->redis($this->cacheConfig->getDb())->hDel(self::prefix . $nameSpace, $id);
    }

    public function removeNameSpace(string $nameSpace)
    {
        return $this->redis($this->cacheConfig->getDb())->del(self::prefix . $nameSpace);
    }

    public function get(string $id)
    {
        return $this->redis($this->cacheConfig->getDb())->get(self::prefix . $id);
    }

    public function set(string $id, string $data, int $time)
    {
        //如果缓存时间是默认值，则给缓存时间增加一个0-20%的浮动，避免大量缓存同时过期
        if ($time == 0) {
            $time = $this->cacheConfig->getTimeout();
            $time = mt_rand($time, ceil($time *0.2) + $time);
        }
        if ($time > 0) {
            return $this->redis($this->cacheConfig->getDb())->setex(self::prefix . $id, $time, $data);
        } else {
            return $this->redis($this->cacheConfig->getDb())->set(self::prefix . $id, $data);
        }
    }

    public function remove(string $id)
    {
        $this->redis($this->cacheConfig->getDb())->del(self::prefix . $id);
    }


    public function lock(string $resource, int $ttl = 1000)
    {
        $resource = 'LOCK_' . $resource;
        $token = uniqid();
        if($this->redis($this->cacheConfig->getDb())->set($resource, $token, ['NX', 'PX' => $ttl])){
            $this->debug("cache lock:" . $resource .', token :'. $token);
            return $token;
        }
        $this->debug("cache lock fail" . $resource);
        return false;
    }


    public function unlock(string $resource, string $token)
    {
        $resource = 'LOCK_' . $resource;
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        $result =  $this->redis($this->cacheConfig->getDb())->eval($script, [$resource, $token], 1);
        $this->debug('cache unlock :' . $resource . ', token:'.$token. ', result:'.$result);
        return $result;
    }

}