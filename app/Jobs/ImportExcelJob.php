<?php

namespace App\Jobs;

use App\Core\Constructors\Model\BaseModel;
use App\Core\Excel\Importer;
use App\Models\System\SystemTask;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ImportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private array $data = [
        'file_path' => '',
        'task_uuid' => '',  //任务uuid,
        'user_id' => -1,
        'user_uuid' => '',
        'force' => false,
        'importer' => '',
    ];

    private $task;

    private array $progress = [];

    public function __construct(string $taskUuid, string $importer, array $option = [])
    {
        //

        $this->data['task_uuid'] = $taskUuid;
        $this->data['importer'] = $importer;
        isset($option['user_id']) && $this->data['user_id'] = $option['user_id'];
        isset($option['user_uuid']) && $this->data['user_uuid'] = $option['user_uuid'];
        isset($option['force']) && $this->data['force'] = $option['force'];
        isset($option['file_path']) && $this->data['file_path'] = $option['file_path'];
    }

    /**
     *
     * @param $now
     * @param $total
     * @param $status
     * @param string $message
     * Date : 2021/4/30 21:37
     * Author : 孤鸿渺影
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function setProgress($now, $total, $status, $startTime = 0, $message = '')
    {
        $this->progress = [
            'status' => $status,
            'now' => $now,
            'total' => $total,
            'message' => $message,
            'startTime' => $startTime
        ];
        Cache::tags('TaskProgress')->put($this->data['task_uuid'], $this->progress);
    }

    /**
     *
     * @param Model $task
     * @param $now
     * @param $total
     * @param $status
     * @param int $startTime
     * @param string $message
     * Date : 2021/4/30 21:57
     * Author : 孤鸿渺影
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function setProgressAndModel($task, $now, $total, $status, $startTime = 0, $message = '')
    {
        $task->status = $status;
        $task->content = $message;
        $task->progress = $now . '/' . $total;
        $task->save();
        $this->setProgress($now, $total, $status, $startTime, $message);
    }

    /**
     *
     * @param $now
     * @param $total
     * @param $status
     * @param string $message
     * Date : 2021/5/6 20:37
     * Author : 孤鸿渺影
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function appendProgress($now, $total, $status, $message = '')
    {
        $this->progress['now'] = $now;
        $this->progress['total'] = $total;
        $this->progress['status'] = $status;
        $this->progress['message'] .= "\n" . $message;
        Cache::tags('TaskProgress')->put($this->data['task_uuid'], $this->progress);
    }

    /**
     *
     * Date : 2021/4/30 21:43
     * Author : 孤鸿渺影
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        //
        $startTime = time();
        $systemTaskModel = new SystemTask();
        $task = $systemTaskModel->findUuid($this->data['task_uuid']);
        if (!$task) {
            $this->setProgress( 0, 100, 3, $startTime, '任务信息错误');
            return  false;
        }

        if (!class_exists($this->data['importer'])) {
            $this->setProgressAndModel($task, 0, 100, 3, $startTime, '导入实例找不到');
            return false;
        }

        $this->setProgressAndModel($task, 0, 100, 1, $startTime, '任务开始执行');

        /* @var $importer Importer */
        $importer = new $this->data['importer']();
        /* @var $model BaseModel */
        $model = $importer->getModel();

        $force = $this->data['force'];

        $filePath = $this->data['file_path'];
        if (!file_exists(base_path($filePath))) {
            $this->setProgressAndModel($task, 0, 100, 3, $startTime, sprintf('导入Excel文件失踪:[%s]', $filePath));
            return false;
        }
        $list = Excel::toArray(null, $filePath)[0];
        $total = sizeof($list) - 1;

        foreach ($list as $key => $row) {
            if ($key === 0) {
                // 标题行不作处理
                continue;
            }
            $data = $importer->formatter($row);

            $validator = $model->createValidate($data);

            if(!$validator->isSuccess()) {
                if ($force) {
                    $message = '第' . ($key) . '行导入失败:' . '<font style="color: red">' . $validator->getMessage() . '</font>';
                    $this->appendProgress($key, $total, 1,$message );
                } else {
                    $this->setProgressAndModel($task, $key, $total, 2, $startTime, $validator->getMessage());
                    return false;
                }
            }else{
                /* @var $model Builder */
                $model->create($validator->getData());
            }
        }
        $this->setProgressAndModel($task, $total, $total, 2, $startTime, '导入Excel文件成功');
        localDisk()->del($filePath);

    }

}
