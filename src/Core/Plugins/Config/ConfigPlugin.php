<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/29
 * Time: 14:16
 */

namespace ESD\Core\Plugins\Config;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Plugins\Event\EventPlugin;
use ESD\Core\Server\Server;
use Symfony\Component\Yaml\Yaml;

class ConfigPlugin extends AbstractPlugin
{
    //手动设置的Config配置
    const ConfigDeep = 10;
    //bootstrap.yml
    const BootstrapDeep = 9;
    //application.yml
    const ApplicationDeep = 8;
    //application-active.yml
    const ApplicationActiveDeep = 7;
    //远程全局Application配置
    const ConfigServerGlobalApplicationDeep = 6;
    //远程Application配置
    const ConfigServerApplicationDeep = 5;
    //远程Application/Active配置
    const ConfigServerApplicationActiveDeep = 4;
    /**
     * @var ConfigConfig
     */
    protected $configConfig;

    /**
     * @var ConfigContext
     */
    protected $configContext;

    /**
     * ConfigPlugin constructor.
     * @param ConfigConfig|null $configConfig
     * @throws \ESD\Core\Exception
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(?ConfigConfig $configConfig = null)
    {
        parent::__construct();
        if ($configConfig == null) {
            if (defined("RES_DIR")) {
                $path = RES_DIR;
            } else {
                $path = Server::$instance->getServerConfig()->getRootDir() . "/resources";
            }
            $configConfig = new ConfigConfig($path);
        }
        $this->configConfig = $configConfig;
        $this->configContext = DIGet(ConfigContext::class);
        $this->atAfter(EventPlugin::class);
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Config";
    }

    /**
     * 在服务启动前
     * @param Context $context
     */
    public function beforeServerStart(Context $context)
    {
        $bootstrapFile = $this->configConfig->getConfigDir() . "/bootstrap.yml";
        if (is_file($bootstrapFile)) {
            $this->configContext->addDeepConfig(Yaml::parseFile($bootstrapFile), self::BootstrapDeep);
        }
        $applicationFile = $this->configConfig->getConfigDir() . "/application.yml";
        if (is_file($applicationFile)) {
            $this->configContext->addDeepConfig(Yaml::parseFile($applicationFile), self::ApplicationDeep);
        }
        $active = $this->configContext->get("esd.profiles.active");
        if (!empty($active)) {
            $applicationActiveFile = $this->configConfig->getConfigDir() . "/application-{$active}.yml";
            if (is_file($applicationActiveFile)) {
                $this->configContext->addDeepConfig(Yaml::parseFile($applicationActiveFile), self::ApplicationActiveDeep);
            }
        }
    }

    /**
     * 在进程启动前
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }

    /**
     * @return ConfigContext
     */
    public function getConfigContext(): ConfigContext
    {
        return $this->configContext;
    }

}