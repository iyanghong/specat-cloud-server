<?php

namespace App\Console\Commands\Swoole\Server;

use App\Console\Commands\Swoole\RemoteProcedureCallServer;
use App\Http\Controllers\TestController;
use App\Remote\System\SystemHandler;
use Illuminate\Console\Command;
use ReflectionClass;
use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;
class TestServer extends RemoteProcedureCallServer
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:test:server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $host = '127.0.0.1';

    protected $port = 9501;
    protected $debug = true;

    protected $handlers = [];
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
        $this->setHandler('Test',TestController::class);
        return parent::handle();
    }

    protected function setHandler($name,$handler)
    {
        $this->handlers[$name] = $handler;
    }

    protected function receive(Connection $connection, $data)
    {

        try{
            $ref = new ReflectionClass($this->handlers['Test']);
            $handler = $ref->newInstance();
            return $handler->test();
        }catch (\Exception $exception){
            return fail($exception->getMessage());
        }
    }
    protected function workerStart(Coroutine\Server $server)
    {
        // TODO: Implement workerStart() method.
    }

}
