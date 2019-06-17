<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/30
 * Time: 9:46
 */

namespace ESD\Core\Plugins\Config;

use ESD\Core\Server\Server;

/**
 * 配置的基础类，命名为驼峰
 * Class BaseConfig
 * @package ESD\Core\Plugins\Config
 */
class BaseConfig
{
    use ToConfigArray;

    protected static $uuid = 1000;
    private $configPrefix;
    private $config = [];
    private $isArray;
    private $indexName;

    /**
     * BaseConfig constructor.
     * @param string $prefix
     * @param bool $isArray
     * @param null $indexName
     */
    public function __construct(string $prefix, bool $isArray = false, $indexName = null)
    {
        $this->configPrefix = $prefix;
        $this->isArray = $isArray;
        $this->indexName = $indexName;
    }

    /**
     * 当设置好配置后将合并配置
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function merge()
    {
        $this->config = [];
        $prefix = $this->configPrefix;
        $config = &$this->config;
        //如果是数组那么还要再深入一层
        if ($this->isArray) {
            if ($this->indexName == null) {
                $index = 0;
            } else {
                $indexName = $this->indexName;
                $index = $this->$indexName;
                if (empty($index)) {
                    throw new ConfigException("配置错误无法获取到$indexName");
                }
            }
            $prefix = $prefix . ".$index";
        }
        $prefixs = explode(".", $prefix);
        foreach ($prefixs as $value) {
            $config[$value] = [];
            $config = &$config[$value];
        }
        $config = $this->toConfigArray();
        //添加到配置上下文中
        Server::$instance->getConfigContext()->appendDeepConfig($this->config, ConfigPlugin::ConfigDeep);
        //合并回配置
        $this->config = Server::$instance->getConfigContext()->get($prefix);
        $this->buildFromConfig($this->config);
        //注入DI中
        DISet(get_class($this), $this);
    }
}