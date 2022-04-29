<?php

namespace App\Console\Commands\Swoole;

use Illuminate\Console\Command;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;
use Swoole\Process;

abstract class RemoteProcedureCallServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:server1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'swoole server';

    /**
     * @var  string 远程地址
     */
    protected $host;
    /**
     * @var int 端口
     */
    protected $port;
    /**
     * @var bool 是否开始debug模式
     */
    protected $debug;
    /**
     * @var array 配置
     */
    protected $config;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!extension_loaded('swoole')) {
            dump('undefined swoole extension!');
            return;
        }
        //多进程管理模块
        $pool = new Process\Pool(2);
        //让每个OnWorkerStart回调都自动创建一个协程
        $pool->set(['enable_coroutine' => true]);
        $pool->on('workerStart', function ($pool, $id) {
            //每个进程都监听$this->port端口
            $server = new \Swoole\Coroutine\Server($this->host, $this->port, false, true);
            //收到15信号关闭服务
            Process::signal(SIGTERM, function () use ($server) {
                $server->shutdown();
            });
            //接收到新的连接请求 并自动创建一个协程
            $server->handle(function (Connection $conn) {
                while (true) {
                    //接收数据
                    $data = $conn->recv(1);
                    if ($data === '' || $data === false) {
                        $errCode = swoole_last_error();
                        $errMsg = socket_strerror($errCode);
                        if($this->debug) {
                            $time = date('Y-m-d H:i:s');
                            echo "{$time}:  errorCode: {$errCode}, errorMessage: {$errMsg}" . PHP_EOL;
                        }
                        $conn->close();
                        break;
                    }

                    try{
                        dump($data);
//                        $data = json_decode($data,true);

                        $result = $this->receive($conn,$data);
                        //发送数据
                        $conn->send($result);
                    }catch (\Exception $exception){
                        if($this->debug){
                            $this->printLog("Throw Exception:errorCode:  {$exception->getCode()},  errorMessage: {$exception->getMessage()}");
                        }
                    }

                    Coroutine::sleep(1);
                }
            });

            $this->workerStart($server);

            //开始监听端口
            $server->start();
        });
        $this->beforeServerStart();
        $pool->start();
    }

    public function beforeServerStart()
    {
        echo "-------------------------------------------" . PHP_EOL;
        echo "     Socket服务器开启 端口：" . $this->port . PHP_EOL;
        echo "-------------------------------------------" . PHP_EOL;
    }

    protected function printLog($msg)
    {
        $time = date('Y-m-d H:i:s');
        echo "----------------------{$time}---------------------" . PHP_EOL;
        echo "{$msg}" . PHP_EOL;
    }

    abstract protected function workerStart(\Swoole\Coroutine\Server $server);

    abstract protected function receive(Connection $connection, $data);
}
