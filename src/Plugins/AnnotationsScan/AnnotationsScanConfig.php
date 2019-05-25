<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/13
 * Time: 17:19
 */

namespace ESD\Plugins\AnnotationsScan;


use ESD\Core\Config\BaseConfig;

class AnnotationsScanConfig extends BaseConfig
{
    const key = "scan";
    /**
     * @var string[]
     */
    protected $includePaths = [];

    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return string[]
     */
    public function getIncludePaths()
    {
        return $this->includePaths;
    }

    /**
     * @param string[] $includePath
     */
    public function setIncludePaths($includePath)
    {
        $this->includePaths = $includePath;
    }

    /**
     * @param string $includePath
     */
    public function addIncludePath(string $includePath)
    {
        $includePath = realpath($includePath);
        if ($includePath === false) return;
        $key = str_replace(realpath(ROOT_DIR),"",$includePath);
        $key = str_replace("/",".",$key);
        $this->includePaths[$key] = $includePath;
    }
}