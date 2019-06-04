<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/4
 * Time: 15:45
 */

namespace ESD\Psr\DB;


interface DBInterface
{
    public function getType();

    public function execute(callable $call = null);

    public function getLastQuery();
}