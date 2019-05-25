<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 10:12
 */

namespace ESD\Plugins\Mysql;


class MysqliDbProxy
{
    use GetMysql;
    public function __get($name)
    {
        return $this->mysql()->$name;
    }

    public function __set($name, $value)
    {
        $this->mysql()->$name = $value;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->mysql(), $name], $arguments);
    }
}