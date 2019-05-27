<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/24
 * Time: 16:15
 */

namespace ESD\Core\DI;


use DI\ContainerBuilder;

class DI
{
    public static $definitions = [];
    /**
     * @var DI
     */
    private static $instance;
    /**
     * @var \DI\Container
     */
    private $container;

    /**
     * DI constructor.
     * @throws \Exception
     */
    public function __construct()
    {
       /* $cacheProxiesDir = ROOT_DIR . '/bin/cache/proxies';
        if (!file_exists($cacheProxiesDir)) {
            mkdir($cacheProxiesDir, 0777, true);
        }
        $cacheDir = ROOT_DIR . "/bin/cache/di";
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }*/
        $builder = new ContainerBuilder();
     /*   $builder->enableCompilation($cacheDir);
        $builder->writeProxiesToFile(true, $cacheProxiesDir);*/
        $builder->addDefinitions(self::$definitions);
        $builder->useAnnotations(true);
        $this->container = $builder->build();
    }

    /**
     * @return DI
     * @throws \Exception
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new DI();
        }
        return self::$instance;
    }

    /**
     * @return \DI\Container
     */
    public function getContainer(): \DI\Container
    {
        return $this->container;
    }
}