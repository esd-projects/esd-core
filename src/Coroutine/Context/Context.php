<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 12:37
 */

namespace ESD\Coroutine\Context;

use ArrayObject;
use Exception;

/**
 * 上下文
 * Class Context
 */
class Context
{

    const SERVER_CONTEXT = -1;

    const PROCESS_CONTEXT = -2;

    /**
     * @var Context[]
     */
    public static $contextStack = [];

    /**
     * @var ArrayObject
     */
    public static $instance;

    /**
     * @param int $id
     * @param Context $context
     * @param bool $overwrite
     * @throws Exception
     */
    public static function registerContext(int $id, Context $context, $overwrite = false)
    {
        if(isset(self::$contextStack[$id]) && !$overwrite) {
            throw new Exception("已经存在相同id的上下文");
        }
        self::$contextStack[$id] = $context;
    }

    const storageKey = "@context";
    /**
     * @var array
     */
    protected $contain = [];
    /**
     * @var array
     */
    protected $classContain = [];

    /**
     * @var Context
     */
    protected $parentContext;


    /**
     * Context constructor.
     * @param Context|null $parentContext
     */
    public function __construct(Context $parentContext = null)
    {
        $this->parentContext = $parentContext;
    }

    /**
     * 添加
     * @param $name
     * @param $value
     * @param $overwrite
     * @throws Exception
     */
    public function add($name, $value, $overwrite = false)
    {
        if ($value == null) return;
        if (isset($this->contain[$name]) && !$overwrite) {
            throw new Exception("已经存在相同名字的上下文");
        }
        $this->contain[$name] = $value;
        if (!is_string($value) && !is_int($value) && !is_bool($value) && !is_float($value) && !is_double($value) && !is_array($value) && !is_callable($value) && !is_long($value)) {
            $this->classContain[get_class($value)] = $value;
        }
    }

    /**
     * 通过类名获取
     * @param $className
     * @return mixed|null
     */
    public function getByClassName($className)
    {
        return $this->classContain[$className] ?? null;
    }

    /**
     * 通过类名递归获取
     * @param $className
     * @return mixed|null
     */
    public function getDeepByClassName($className)
    {
        $result = $this->classContain[$className] ?? null;
        if ($result == null && $this->parentContext != null) {
            return $this->parentContext->getDeepByClassName($className);
        }
        return $result;
    }

    /**
     * 获取
     * @param $name
     * @return mixed|null
     */
    public function get($name)
    {
        return $this->contain[$name] ?? null;
    }

    /**
     * 递归父级获取
     * @param $name
     * @return null
     */
    public function getDeep($name)
    {
        $result = $this->contain[$name] ?? null;
        if ($result == null && $this->parentContext != null) {
            return $this->parentContext->getDeep($name);
        }
        return $result;
    }

    /**
     * @param Context $parentContext
     */
    public function setParentContext(Context $parentContext): void
    {
        $this->parentContext = $parentContext;
    }

    /**
     * @return Context|null
     */
    public function getParentContext()
    {
        return $this->parentContext;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->contain);
    }
}