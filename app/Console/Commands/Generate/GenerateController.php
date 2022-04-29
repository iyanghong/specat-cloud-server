<?php

namespace App\Console\Commands\Generate;

use App\Core\Generate\Resource\Controller;
use App\Core\Generate\Resource\ResourceFile;
use App\Core\Generate\Table\Importer;
use Illuminate\Console\Command;

class GenerateController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:controller {name} {force=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成控制器';

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

        //生成控制器
        $controller = new Controller($resourceFile);
        $controller->setForce($force)->handle();
        if (!$controller->isSuccess()) {
            $this->error($controller->getMessage());
            return false;
        }
        $this->info(sprintf("Controller `%sController` 写入成功!", $resourceFile->getName()));
        return true;
    }
}
