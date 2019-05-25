<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 10:03
 */

namespace ESD\Plugins\Pack;


class ClientDataProxy
{
    use GetClientData;
    public function __get($name)
    {
        return $this->getClientData()->$name;
    }

    public function __set($name, $value)
    {
        $this->getClientData()->$name = $value;
    }

    public function __call($name, $arguments)
    {
        if ($this->getClientData() == null) {
            return null;
        }
        return call_user_func_array([$this->getClientData(), $name], $arguments);
    }
}