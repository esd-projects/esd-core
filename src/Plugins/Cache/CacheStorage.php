<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/7
 * Time: 14:43
 */

namespace ESD\Plugins\Cache;


interface CacheStorage
{
    public function getFromNameSpace(string $nameSpace, string $id);
    public function setFromNameSpace(string $nameSpace, string $id, string $data);
    public function removeFromNameSpace(string $nameSpace, string $id);
    public function removeNameSpace(string $nameSpace);
    public function get(string $id);
    public function set(string $id,string $data,int $time);
    public function remove(string $id);
    public function lock(string $id , int $ttl);
    public function unlock(string $id, string $token);
}