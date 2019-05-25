<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 9:55
 */

namespace ESD\Plugins\Mysql;


use ESD\Core\Channel\Channel;

class MysqlPool
{
    /**
     * @var Channel
     */
    protected $pool;
    /**
     * @var MysqlConfig
     */
    protected $mysqlConfig;

    /**
     * MysqlPool constructor.
     * @param MysqlConfig $mysqlConfig
     * @throws MysqlException
     */
    public function __construct(MysqlConfig $mysqlConfig)
    {
        $this->mysqlConfig = $mysqlConfig;
        $config = $mysqlConfig->buildConfig();
        $this->pool = DIGet(Channel::class, [$mysqlConfig->getPoolMaxNumber()]);
        for ($i = 0; $i < $mysqlConfig->getPoolMaxNumber(); $i++) {
            $db = new MysqliDb($config);
            $this->pool->push($db);
        }
    }

    /**
     * @return MysqliDb
     */
    public function db(): MysqliDb
    {
        $db = getContextValue("MysqliDb:{$this->getMysqlConfig()->getName()}");
        if ($db == null) {
            $db = $this->pool->pop();
            defer(function () use ($db) {
                $this->pool->push($db);
            });
            setContextValue("MysqliDb:{$this->getMysqlConfig()->getName()}", $db);
        }
        return $db;
    }

    /**
     * @return MysqlConfig
     */
    public function getMysqlConfig(): MysqlConfig
    {
        return $this->mysqlConfig;
    }

    /**
     * @param MysqlConfig $mysqlConfig
     */
    public function setMysqlConfig(MysqlConfig $mysqlConfig): void
    {
        $this->mysqlConfig = $mysqlConfig;
    }

}