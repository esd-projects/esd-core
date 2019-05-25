<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 10:59
 */

namespace ESD\Plugins\Mysql;


class MysqlManyPool
{
    protected $poolList = [];

    /**
     * 获取连接池
     * @param $name
     * @return MysqlPool|null
     */
    public function getPool($name = "default")
    {
        return $this->poolList[$name] ?? null;
    }

    /**
     * 添加连接池
     * @param MysqlPool $mysqlPool
     */
    public function addPool(MysqlPool $mysqlPool)
    {
        $this->poolList[$mysqlPool->getMysqlConfig()->getName()] = $mysqlPool;
    }

    /**
     * @return MysqliDb
     * @throws MysqlException
     */
    public function db(): MysqliDb
    {
        $default = $this->getPool();
        if ($default == null) {
            throw new MysqlException("没有设置默认的mysql");
        }
        return $this->getPool()->db();
    }
}