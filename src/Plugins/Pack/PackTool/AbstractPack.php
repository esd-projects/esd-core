<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 14:17
 */

namespace ESD\Plugins\Pack\PackTool;


use ESD\Core\Server\Config\PortConfig;
use ESD\Plugins\Pack\PackException;

abstract class AbstractPack implements IPack
{
    /**
     * @var PortConfig
     */
    protected $portConfig;

    /**
     * 获取长度
     * c：有符号、1字节
     * C：无符号、1字节
     * s ：有符号、主机字节序、2字节
     * S：无符号、主机字节序、2字节
     *  n：无符号、网络字节序、2字节
     *  N：无符号、网络字节序、4字节
     * l：有符号、主机字节序、4字节（小写L）
     * L：无符号、主机字节序、4字节（大写L）
     * v：无符号、小端字节序、2字节
     * V：无符号、小端字节序、4字节
     * @param string $type
     * @return int
     * @throws PackException
     */
    protected function getLength(string $type)
    {
        switch ($type) {
            case "c":
                return 1;
            case "C":
                return 1;
            case "s":
                return 2;
            case "S":
                return 2;
            case "n":
                return 2;
            case "N":
                return 4;
            case "l":
                return 4;
            case "L":
                return 4;
            case "v":
                return 2;
            case "V":
                return 4;
            default:
                throw new PackException('错误的类型');
        }
    }
}