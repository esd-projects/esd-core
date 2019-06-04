<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/4
 * Time: 15:45
 */

namespace ESD\Core\DB;


interface DBInterface
{
    public function getType(): string;

    public function rawQuery($query, $bindParams = null);
}