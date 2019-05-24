<?php

use ESD\ExampleClass\Port\SwoolePort;
use ESD\ExampleClass\Process\SwooleProcess;
use ESD\ExampleClass\SwooleApplication;
use ESD\Core\Server\Config\ServerConfig;
use ESD\Core\Server\Server;

require __DIR__ . '/../vendor/autoload.php';


define("ROOT_DIR", __DIR__ . "/src");
define("RES_DIR", __DIR__ . "/resources");

$server = new Server(
    new ServerConfig(),
    SwooleApplication::class,
    SwoolePort::class,
    SwooleProcess::class);

$server->configure();
$server->start();