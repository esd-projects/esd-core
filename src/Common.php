<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 17:58
 */

use Swoole\Timer;


/**
 * 序列化
 * @param $data
 * @return string
 */
function serverSerialize($data)
{
    return serialize($data);
}

/**
 * 反序列化
 * @param string $data
 * @return mixed
 */
function serverUnSerialize(string $data)
{
    return unserialize($data);
}

/**
 * 添加一个定时器
 * @param int $msec
 * @param callable $callback
 * @param array $params
 * @return int
 */
function addTimerTick(int $msec, callable $callback, ... $params)
{
    return Timer::tick($msec, $callback, ...$params);
}

/**
 * 清除一个定时器
 * @param int $timerId
 * @return bool
 */
function clearTimerTick(int $timerId)
{
    return Timer::clear($timerId);
}

/**
 * 添加一个定时器
 * @param int $msec
 * @param callable $callback
 * @param array $params
 * @return int
 */
function addTimerAfter(int $msec, callable $callback, ... $params)
{
    return Timer::after($msec, $callback, ...$params);
}

/**
 * 删目录
 * @param null $path
 */
function clearDir($path = null)
{
    if (is_dir($path)) {    //判断是否是目录
        $p = scandir($path);     //获取目录下所有文件
        foreach ($p as $value) {
            if ($value != '.' && $value != '..') {    //排除掉当./和../
                if (is_dir($path . '/' . $value)) {
                    clearDir($path . '/' . $value);    //递归调用删除方法
                    rmdir($path . '/' . $value);    //删除当前文件夹
                } else {
                    unlink($path . '/' . $value);    //删除当前文件
                }
            }
        }
    }
}