<?php

namespace App\Console\Commands\Swoole\Test;


use App\Remote\System\SystemClient;
use Illuminate\Console\Command;

class GetServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:getServer';

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
        $result = SystemClient::getInstance()->getTest();
        dump($result);
    }
}
