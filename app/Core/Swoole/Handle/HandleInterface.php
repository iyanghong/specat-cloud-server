<?php


namespace App\Core\Swoole\Handle;


use swoole_server;

interface HandleInterface
{
    public function __construct(swoole_server $server, $fd, $reactorId);

}