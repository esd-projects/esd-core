<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 16-7-15
 * Time: 下午2:43
 */

namespace ESD\Plugins\Pack\PackTool;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Config\PortConfig;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Pack\PackException;

/**
 * 不支持package_length_offset
 * Class LenJsonPack
 * @package ESD\Plugins\EasyRoute\PackTool
 */
class LenJsonPack extends AbstractPack
{
    use GetLogger;

    /**
     * 数据包编码
     * @param string $buffer
     * @return string
     * @throws PackException
     */
    public function encode(string $buffer)
    {
        $total_length = $this->getLength($this->portConfig->getPackageLengthType()) + strlen($buffer) - $this->portConfig->getPackageBodyOffset();
        return pack($this->portConfig->getPackageLengthType(), $total_length) . $buffer;
    }

    /**
     * @param $buffer
     * @return string
     * @throws PackException
     */
    public function decode(string $buffer)
    {
        return substr($buffer, $this->getLength($this->portConfig->getPackageLengthType()));
    }

    /**
     * @param $data
     * @param PortConfig $portConfig
     * @param string|null $topic
     * @return string
     * @throws PackException
     */
    public function pack(string $data, PortConfig $portConfig, ?string $topic = null)
    {
        $this->portConfig = $portConfig;
        return $this->encode(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param int $fd
     * @param string $data
     * @param PortConfig $portConfig
     * @return ClientData
     * @throws PackException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function unPack(int $fd, string $data, PortConfig $portConfig): ?ClientData
    {
        $this->portConfig = $portConfig;
        $value = json_decode($this->decode($data), true);
        if (empty($value)) {
            $this->warn('json unPack 失败');
            return null;
        }
        $clientData = new ClientData($fd, $portConfig->getBaseType(), $value['p'], $value);
        return $clientData;
    }
}