<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 10:49
 */

namespace ESD\Plugins\Redis;


use ESD\Core\Plugins\Config\BaseConfig;

class RedisConfig extends BaseConfig
{
    const key = "redis";
    /**
     * @var string
     */
    protected $name;
    /**
     * @var int
     */
    protected $poolMaxNumber;
    /**
     * @var string
     */
    protected $host;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var int
     */
    protected $selectDb;
    /**
     * @var int
     */
    protected $port;

    /**
     * RedisConfig constructor.
     * @param string $host
     * @param string $password
     * @param int $selectDb
     * @param int|null $port
     * @param string $name
     * @param int $poolMaxNumber
     * @throws \ReflectionException
     */
    public function __construct(string $host, string $password = "", int $selectDb = 0, $port = 6379, string $name = "default", int $poolMaxNumber = 10)
    {
        parent::__construct(self::key);
        $this->name = $name;
        $this->poolMaxNumber = $poolMaxNumber;
        $this->host = $host;
        $this->password = $password;
        $this->selectDb = $selectDb;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPoolMaxNumber(): int
    {
        return $this->poolMaxNumber;
    }

    /**
     * @param int $poolMaxNumber
     */
    public function setPoolMaxNumber(int $poolMaxNumber): void
    {
        $this->poolMaxNumber = $poolMaxNumber;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * 构建配置
     * @throws RedisException
     */
    public function buildConfig()
    {
        if (!extension_loaded('redis')) {
            throw new RedisException("缺少redis扩展");
        }
        if ($this->poolMaxNumber < 1) {
            throw new RedisException("poolMaxNumber必须大于1");
        }
        if(empty($this->name)){
            throw new RedisException("name必须设置");
        }
        if(empty($this->host)){
            throw new RedisException("host必须设置");
        }
    }

    /**
     * @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port): void
    {
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getSelectDb(): int
    {
        return $this->selectDb;
    }

    /**
     * @param int $selectDb
     */
    public function setSelectDb(int $selectDb): void
    {
        $this->selectDb = $selectDb;
    }
}