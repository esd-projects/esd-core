<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/10
 * Time: 13:47
 */

namespace ESD\Psr\Cloud;


interface Services
{
    public function getServices(string $service): ?array;
}