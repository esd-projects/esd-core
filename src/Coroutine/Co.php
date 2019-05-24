<?php


namespace ESD\Coroutine;


use ArrayObject;
use ESD\Coroutine\Context\Context;
use ESD\Coroutine\Pool\Runnable;
use Exception;
use Iterator;
use Swoole\Coroutine;

class Co
{

    public static function isCoroutine()
    {
        return class_exists("\Swoole\Coroutine") && getenv('use_coroutine') == 1;
    }

    /**
     * 协程设置
     * @param $data
     */
    public static function set($data): void
    {
        if (self::isCoroutine()) {
            Coroutine::set($data);
        }
    }

    /**
     * 获取协程状态
     * @return array
     */
    public static function getStats(): array
    {
        if (self::isCoroutine()) {
            return Coroutine::stats();
        } else {
            return [];
        }
    }

    /**
     * 判断指定协程是否存在
     * @param $coId
     * @return bool
     */
    public static function exists($coId): bool
    {
        if (self::isCoroutine()) {
            return Coroutine::exists($coId);
        }else {
            return false;
        }
    }

    /**
     * 获取当前协程的唯一ID, 它的别名为getUid, 是一个进程内唯一的正整数
     * @return int
     */
    public static function getCid(): int
    {
        if (self::isCoroutine()) {
            return Coroutine::getCid();
        }else {
            return -1;
        }
    }

    /**
     * 获取当前协程的父协程ID
     * @return int
     */
    public static function getPcid(): int
    {
        if (self::isCoroutine()) {
            return Coroutine::getPcid();
        }else {
            return -1;
        }
    }

    /**
     * 获取当前协程的上下文对象
     * @return ArrayObject
     */
    public static function getSwooleContext()
    {
        if (self::isCoroutine()) {
            return Coroutine::getContext();
        }else {
            if (Context::$instance == null) {
                Context::$instance = new ArrayObject();
            }
            return Context::$instance;
        }
    }

    /**
     * 获取当前协程的上下文对象
     * @return Context
     */
    public static function getContext(): Context
    {
        $result = self::getSwooleContext()[Context::storageKey] ?? null;
        if ($result == null) {
            $parentContext = null;
            foreach (Context::$contextStack as $context) {
                $parentContext = $context;
                break;
            }
            self::getSwooleContext()[Context::storageKey] = new Context($parentContext);
        }
        return self::getSwooleContext()[Context::storageKey];
    }

    /**
     * 遍历当前进程内的所有协程。
     * @return Iterator
     * @throws Exception
     */
    public static function getListCoroutines()
    {
        if (self::isCoroutine()) {
            return Coroutine::listCoroutines();
        } else {
            throw new Exception("Co::getListCoroutines need running in coroutine environment");
        }
    }

    /**
     * 获取协程函数调用栈。
     * @param int $cid
     * @param int $options
     * @param int $limit
     * @return array
     */
    public static function getBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array
    {
        if (self::isCoroutine()) {
            return Coroutine::getBackTrace($cid, $options, $limit);
        }
        return [];
    }

    /**
     * 让出当前协程的执行权。
     */
    public static function yield()
    {
        if (self::isCoroutine()) {
            Coroutine::yield();
        }
    }

    /**
     * sleep
     * @param float $se
     */
    public static function sleep(float $se)
    {
        if (self::isCoroutine()) {
            Coroutine::sleep($se);
        }
    }


    /**
     * 让出当前协程的执行权。
     * @param int $coroutineId
     */
    public static function resume(int $coroutineId)
    {
        if (self::isCoroutine()) {
            Coroutine::resume($coroutineId);
        }
    }

    /**
     * 执行任务
     * @param $runnable
     * @return int|bool
     */
    public static function runTask($runnable)
    {
        if (self::isCoroutine()) {
            $cid = goWithContext(function () use ($runnable) {
                if ($runnable != null) {
                    if ($runnable instanceof Runnable) {
                        $result = $runnable->run();
                        $runnable->sendResult($result);
                    }
                    if (is_callable($runnable)) {
                        $runnable();
                    }
                }
            });
            return $cid;
        } else {
            if ($runnable != null) {
                if ($runnable instanceof Runnable) {
                    $result = $runnable->run();
                    $runnable->sendResult($result);
                }
                if (is_callable($runnable)) {
                    $runnable();
                }
            }
            return 0;
        }

    }
}