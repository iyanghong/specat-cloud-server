<?php

namespace App\Console\Commands\Generate;

use App\Core\Generate\Resource\Controller;
use App\Core\Generate\Resource\Model;
use App\Core\Generate\Resource\ResourceFile;
use App\Core\Generate\Resource\Route;
use App\Core\Generate\Table\Importer;
use Illuminate\Console\Command;

class GenerateAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:all {name} {force=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成全局';

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
     * @return bool|int
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
        //生成模型
        $model = new Model($resourceFile, $table);
        $model->setForce($force)->handle();
        if (!$model->isSuccess()) {
            $this->error($model->getMessage());
            return false;
        }
        $this->info(sprintf("Model `%sModel` 写入成功!", $resourceFile->getName()));
        //生成控制器
        $controller = new Controller($resourceFile);
        $controller->setForce($force)->handle();
        if (!$controller->isSuccess()) {
            $this->error($controller->getMessage());
            return false;
        }
        $this->info(sprintf("Controller `%sController` 写入成功!", $resourceFile->getName()));
        //生成路由
        $router = new Route($resourceFile);
        $router->setForce($force)->handle();
        if (!$router->isSuccess()) {
            $this->error($router->getMessage());
            return false;
        }
        $this->info("route写入成功!");
        return false;
    }
}
