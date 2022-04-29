<?php

namespace App\Console\Commands\Swoole\Test;

use App\Remote\System\SystemClient;
use Illuminate\Console\Command;
use Swoole\Coroutine\Client;

class TestServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:connectServer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        \Swoole\Coroutine::create(function () {
            $client = new Client(SWOOLE_SOCK_TCP);
            if (!$client->connect('127.0.0.1', 11301, 0.5))
            {
                echo "connect failed. Error: {$client->errCode}\n";
            }
            $client->send("hello world\n");
            echo $client->recv();
            $client->close();
        });
    }
}
