<?php


namespace App\Core\Swoole\Client;


use App\Core\Swoole\Client;

use Exception;

class RemoteClient implements RemoteClientInterface
{
    protected static $_instances = [];

    protected $service;

    protected $host;

    protected $port;

    protected $client;

    const TIMEOUT = 0.1;

    public function __construct()
    {
        if (!isset($this->service)) {
            throw new Exception('The service name is required!');
        }

        if (!isset($this->host)) {
            throw new Exception('The host is required!');
        }

        if (!isset($this->port)) {
            throw new Exception('The port is required!');
        }

        $client = new \swoole_client(SWOOLE_SOCK_TCP);
        if (!$client->connect($this->host, $this->port, static::TIMEOUT)) {
            throw new Exception("connect failed. Error: {$client->errCode}");
        }
        $this->client = $client;
    }

}