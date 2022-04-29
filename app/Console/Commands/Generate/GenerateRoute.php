<?php

namespace App\Console\Commands\Generate;

use App\Core\Generate\Resource\ResourceFile;
use App\Core\Generate\Resource\Route;
use App\Core\Generate\Table\Importer;
use Illuminate\Console\Command;

class GenerateRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:route {name} {force=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成路由';

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
     *
     * Date : 2021/4/26 12:40
     * Author : 孤鸿渺影
     * @return bool
     * @throws \ErrorException
     */
    public function handle()
    {
        $name = $this->argument('name');
        $force = $this->argument('force');
        $resourceFile = new ResourceFile($name);
        $importer = new Importer();
        $table = $importer->importTable($resourceFile->getTableName());
        if (!$table) {
            $this->error($importer->getMsg());
            return false;
        }
        $resourceFile->setTableInfo(str_replace('表', '', $table->getInfo()));
        //生成路由
        $router = new Route($resourceFile);
        $router->setForce($force)->handle();
        if (!$router->isSuccess()) {
            $this->error($router->getMessage());
            return false;
        }
        $this->info("route写入成功!");
        return true;
    }
}
