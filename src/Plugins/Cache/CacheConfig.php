<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 11:08
 */

namespace ESD\Plugins\Cache;


use ESD\Core\Plugins\Config\BaseConfig;

class CacheConfig extends BaseConfig
{
    const key = "cache";
    /**
     *
     * 销毁时间s
     * @var int
     */
    protected $timeout = 30 * 60;

    /**
     * @var string
     */
    protected $db = "default";
    /**
     * @var string
     */
    protected $cacheStorageClass = RedisCacheStorage::class;


    /**
     * 缓存写入加锁，读取锁等待时间。 单位（毫秒）
     * 如果设置为0则不启用读写锁
     * 高并发建议开启该设置，并且调高redis连接池以及redis的连接数。
     * 如果每秒并发2000，超时设置为3秒，那么会有2000个连接等待。
     * 如果秒级set 建议设置3000
     * 如果几百毫秒set  建议设置 1000
     * 如果不到100毫秒set 建议设置 500
     * @var int
     */
    protected  $lockTimeout = 0;


    /**
     * 读取锁等待重试时长，单位(毫秒)。
     * 每隔 lockWait 重试，如果缓存写入需要秒级，建议调整lockWait为500毫秒以上
     * 如果缓存写入需要 几百毫秒级，建议使用默认 100 毫秒
     * 如果缓存写入需要 一百毫秒以内，建议设置 50毫秒
     * 注意: 此值设置的太低会严重增加redis get负载, 如果每秒并发2000，超时设置3秒，默认锁等待100毫秒，那么最差会有6万get操作
     * 注意：等待时长不要超过lockTimeout，否则会浪费协程资源
     *
     * @var int
     */
    protected $lockWait = 100;


    /**
     * 死锁过期时间 单位（毫秒）
     * 注意：该设置一定要大于读取锁等待时间，否则如果锁释放了还没有写入缓存同样会造成缓存穿透
     * @var int
     */
    protected $lockAlive = 10000;

    public function __construct()
    {
        parent::__construct(self::key);
    }


    public function getLockAlive() :int
    {
        return $this->lockAlive;
    }

    public function setLockAlive($lockAlive):void
    {
        $this->lockAlive = $lockAlive;
    }

    public function setLockTimeout( int $timeout): void
    {
        $this->lockTimeout = $timeout;
    }

    public function getLockTimeout() :int
    {
        return $this->lockTimeout;
    }


    public function getLockWait(): int
    {
        return $this->lockWait;
    }

    public function setLockWait($lockwait): void {
        $this->lockWait = $lockwait;
    }


    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getDb(): string
    {
        return $this->db;
    }

    /**
     * @param string $db
     */
    public function setDb(string $db): void
    {
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function getCacheStorageClass(): string
    {
        return $this->cacheStorageClass;
    }

    /**
     * @param string $cacheStorageClass
     */
    public function setCacheStorageClass(string $cacheStorageClass): void
    {
        $this->cacheStorageClass = $cacheStorageClass;
    }
}