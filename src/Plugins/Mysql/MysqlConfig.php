<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 10:49
 */

namespace ESD\Plugins\Mysql;


use ESD\Core\Plugins\Config\BaseConfig;

class MysqlConfig extends BaseConfig
{
    const key = "mysql";
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
    protected $username;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var string
     */
    protected $db;
    /**
     * 表前缀
     * @var string
     */
    protected $prefix;
    /**
     * @var string
     */
    protected $charset;
    /**
     * @var int
     */
    protected $port;

    /**
     * MysqlConfig constructor.
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $db
     * @param string $prefix
     * @param int $port
     * @param string $charset
     * @param string $name
     * @param int $poolMaxNumber
     * @throws \ReflectionException
     */
    public function __construct(string $host, string $username, string $password, string $db, string $prefix = "", int $port = 3306, string $charset = "utf8", string $name = "default", int $poolMaxNumber = 10)
    {
        parent::__construct(self::key,true,"name");
        $this->name = $name;
        $this->poolMaxNumber = $poolMaxNumber;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->db = $db;
        $this->prefix = $prefix;
        $this->charset = $charset;
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
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
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
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * 构建配置
     * @throws MysqlException
     */
    public function buildConfig()
    {
        if (!extension_loaded('mysqli')) {
            throw new MysqlException("缺少mysqli扩展");
        }
        if ($this->poolMaxNumber < 1) {
            throw new MysqlException("poolMaxNumber必须大于1");
        }
        if(empty($this->name)){
            throw new MysqlException("name必须设置");
        }
        if(empty($this->host)){
            throw new MysqlException("host必须设置");
        }
        if(empty($this->username)){
            throw new MysqlException("username必须设置");
        }
        if(empty($this->password)){
            throw new MysqlException("password必须设置");
        }
        if(empty($this->db)){
            throw new MysqlException("db必须设置");
        }
        return ['host' => $this->host,
            'username' => $this->username,
            'password' => $this->password,
            'db' => $this->db,
            'port' => $this->port,
            'prefix' => $this->prefix,
            'charset' => $this->charset];
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }
}