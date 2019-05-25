<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/28
 * Time: 17:24
 */

namespace ESD\Plugins\Mysql;


trait GetMysql
{
    /**
     * @param string $name
     * @return MysqliDb
     * @throws MysqlException
     */
    public function mysql($name = "default")
    {
        $db = getContextValue("MysqliDb:$name");
        if ($db == null) {
            $mysqlPool = getDeepContextValueByClassName(MysqlManyPool::class);
            if ($mysqlPool instanceof MysqlManyPool) {
                $db = $mysqlPool->getPool($name)->db();
                return $db;
            } else {
                throw new MysqlException("没有找到名为{$name}的mysql连接池");
            }
        } else {
            return $db;
        }
    }
}