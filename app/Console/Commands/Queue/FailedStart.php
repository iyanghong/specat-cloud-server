<?php

namespace App\Console\Commands\Queue;

use App\Models\Log\LogVisited;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Redis;

class FailedStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:failed-start';

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
        $key = "ts_horizon:failed_jobs";
        $redis = Redis::connection('default');
        $count = 0;
        while ($redis->zcard($key) > 0) {
            $list = $redis->zrange($key, 0, 0);
            foreach ($list as $itemKey) {
                $item = $redis->hgetall("ts_horizon:" . $itemKey);
                if (empty($item["retried_by"])) {
                    $data = json_decode($item["payload"], true)["data"];
                    $visitor = unserialize($data["command"])->getVisitor();
                    /* @var $logVisitedModel Builder */
                    $logVisitedModel = new LogVisited();
                    $ipDetail = new \App\Service\Log\LogIpDetail();
                    $ipDetail->setIp($visitor['ip']);
                    $visitor['uuid'] = getUuid();
                    $logVisitedModel->create($visitor);
                    $count++;
                    $this->info(sprintf('[%s]-编号[%s]队列执行成功', $count, $itemKey));
                }
                $redis->zremrangebyrank($key, 0, 0);
            }
        }

        return Command::SUCCESS;
    }
}
