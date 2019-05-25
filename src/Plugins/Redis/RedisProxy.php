<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 10:07
 */

namespace ESD\Plugins\Redis;


class RedisProxy
{
    use GetRedis;

    /**
     * @param $name
     * @return mixed
     * @throws RedisException
     */
    public function __get($name)
    {
        return $this->redis()->$name;
    }

    /**
     * @param $name
     * @param $value
     * @throws RedisException
     */
    public function __set($name, $value)
    {
        $this->redis()->$name = $value;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws RedisException
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->redis(), $name], $arguments);
    }
}