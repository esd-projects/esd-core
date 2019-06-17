<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/17
 * Time: 15:53
 */

namespace ESD\Core\Plugins\Config;


use ReflectionClass;

trait ToConfigArray
{
    protected $reflectionClass;

    /**
     * 转换成配置数组
     * @throws \ReflectionException
     */
    public function toConfigArray()
    {
        $config = [];
        if ($this->reflectionClass == null) {
            $this->reflectionClass = new ReflectionClass(static::class);
        }
        foreach ($this->reflectionClass->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() != BaseConfig::class) {
                $varName = $property->getName();
                if ($property->isPrivate()) continue;
                if ($this->$varName !== null) {
                    if (is_array($this->$varName)) {
                        foreach ($this->$varName as $key => $value) {
                            if ($value instanceof BaseConfig) {
                                $config[$this->changeConnectStyle($varName)][$this->changeConnectStyle($key)] = $value->toConfigArray();
                            } else {
                                $config[$this->changeConnectStyle($varName)][$this->changeConnectStyle($key)] = $value;
                            }
                        }
                    } elseif ($this->$varName instanceof BaseConfig) {
                        $config[$this->changeConnectStyle($varName)] = $this->$varName->toConfigArray();
                    } else {
                        $config[$this->changeConnectStyle($varName)] = $this->$varName;
                    }
                }
            }
        }
        return $config;
    }

    /**
     * 驼峰修改为"_"连接
     * @param $var
     * @return mixed
     */
    protected function changeConnectStyle($var)
    {
        if (is_numeric($var)) {
            return $var;
        }
        $result = "";
        for ($i = 0; $i < strlen($var); $i++) {
            $str = ord($var[$i]);
            if ($str > 64 && $str < 91) {
                $result .= "_" . strtolower($var[$i]);
            } else {
                $result .= $var[$i];
            }
        }
        return $result;
    }

    /**
     * 从config中获取配置
     * @param $config
     * @return static
     */
    public function buildFromConfig($config)
    {
        if ($config == null) return $this;
        foreach ($config as $key => $value) {
            $varName = $this->changeHumpStyle($key);
            $func = "set" . ucfirst($varName);
            if (is_callable([$this, $func])) {
                call_user_func([$this, $func], $value);
            }
        }
        return $this;
    }

    /**
     * "_"连接修改为驼峰
     * @param $var
     * @return mixed
     */
    protected function changeHumpStyle($var)
    {
        if (is_numeric($var)) {
            return $var;
        }
        $result = "";
        for ($i = 0; $i < strlen($var); $i++) {
            if ($var[$i] == "_") {
                $i = $i + 1;
                $result .= strtoupper($var[$i]);
            } else {
                $result .= $var[$i];
            }
        }
        return $result;
    }
}