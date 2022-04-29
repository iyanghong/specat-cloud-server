<?php

namespace App\Console\Commands\Deploy;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Update extends Command
{
    private string $gitRemote = 'service';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:update {force=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '系统部署更新';

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
    public function handle(): int
    {
        $force = (bool) $this->argument('force');
        shell_exec("git pull " . $this->gitRemote . " master");
        if($force){
            shell_exec("ts-tool restart supervisor");
        }
        return CommandAlias::SUCCESS;
    }
}
