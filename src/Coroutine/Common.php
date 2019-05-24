<?php

use ESD\Coroutine\Context\Context;
use ESD\Coroutine\Co;
use Swoole\Runtime;

const HOOK_TCP = SWOOLE_HOOK_TCP;//TCP Socket类型的stream
const HOOK_UDP = SWOOLE_HOOK_UDP;//UDP Socket类型的stream
const HOOK_UNIX = SWOOLE_HOOK_UNIX;//Unix Stream Socket类型的stream
const HOOK_UDG = SWOOLE_HOOK_UDG;//Unix Dgram Socket类型的stream
const HOOK_SSL = SWOOLE_HOOK_SSL;//SSL Socket类型的stream
const HOOK_TLS = SWOOLE_HOOK_TLS;//TLS Socket类型的stream
const HOOK_SLEEP = SWOOLE_HOOK_SLEEP;//睡眠函数
const HOOK_FILE = SWOOLE_HOOK_FILE;//文件操作
const HOOK_BLOCKING_FUNCTION = SWOOLE_HOOK_BLOCKING_FUNCTION;// 如gethostbyname等阻塞系统调用
const HOOK_ALL = SWOOLE_HOOK_ALL;//打开所有类型

/**
 * 全局打开Runtime协程调度
 * @param bool $enable
 * @param int $flags
 */
function enableRuntimeCoroutine(bool $enable = true, int $flags = HOOK_ALL ^ HOOK_FILE)
{
    if (Co::isCoroutine()) {
        Runtime::enableCoroutine($enable, $flags);
    }
}

/**
 * 获取上下文值
 * @param $key
 * @return mixed
 */
function getContextValue($key)
{
    return getContext()->get($key);
}

/**
 * 获取上下文值
 * @param $key
 * @return mixed
 */
function getContextValueByClassName($key)
{
    return getContext()->getByClassName($key);
}


/**
 * 获取上下文值
 * @param $key
 * @param $value
 * @param bool $overwrite
 * @throws Exception
 */
function setContextValue($key, $value, $overwrite = false)
{
    getContext()->add($key, $value, $overwrite);
}

/**
 * 递归父级获取上下文值
 * @param $key
 * @return mixed
 */
function getDeepContextValue($key)
{
    return getContext()->getDeep($key);
}

/**
 * 递归父级获取上下文值
 * @param $key
 * @return mixed
 */
function getDeepContextValueByClassName($key)
{
    return getContext()->getDeepByClassName($key);
}


/**
 * 继承父级的上下文
 * @param callable $run
 * @return int
 */
function goWithContext(callable $run)
{
    $context = getContext();
    if (Co::isCoroutine()) {
        return go(function () use ($run, $context) {
            $currentContext = Co::getContext();
            //重新设置他的父类为上级协程
            $currentContext->setParentContext($context);
            $run();
        });
    } else {
        return $run();
    }

}

/**
 * 获取上下文
 * @return Context
 */
function getContext()
{
    $context = null;
    foreach (Context::$contextStack as $item) {
        $context = $item;
        break;
    }
    if (\Co::getCid() > 0) {
        $context = Co::getContext();
    }
    return $context ?? new Context(null);
}