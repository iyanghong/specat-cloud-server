<?php


namespace App\Remote\System;


use App\Core\Swoole\Client\RemoteClient;

class SystemClient extends RemoteClient
{
    protected $service = 'System';

    protected $host = '127.0.0.1';

    protected $port = 9501;

}