<?php


namespace App\Core\Swoole\Handle;


use swoole_server;

class SwooleHandler
{

    /** @var swoole_server */
    protected string $server;

    /** @var int TCP客户端连接的唯一标识符 */
    protected $fd;

    /** @var int TCP连接所在的Reactor线程ID */
    protected $reactorId;

    public function __construct(swoole_server $server, $fd, $reactorId)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }
}