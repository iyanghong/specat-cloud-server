<?php

namespace App\Console\Commands\Log;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Service\Log\StoreIp;
class IpDetailRefresh extends Command
{
    use StoreIp;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipDetail:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新Ip详情';

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
        $list = DB::select('select log_visited.ip from log_visited  where (select count(1) from ip_detail where ip_detail.ip = log_visited.ip) = 0 group by log_visited.ip');
        $list = json_decode(json_encode($list), true);
        $total = sizeof($list);
        $successCount = 0;
        $failCount = 0;
        foreach ($list as $item) {
            try{
                $flag = $this->setIp($item['ip'],function ($data){
                    $message = sprintf('新增[%s]成功：',$data['ip']);
                    $message .= $data['nation'] . $data['province'] . $data['city'] . $data['district'];
                    $this->info($message);
                });
                if($flag){
                    $successCount++;
                }else{
                    $this->error(sprintf('新增[%s]失败。',$item['ip']));
                    $failCount++;
                }
            }catch (\Exception $exception){
                $this->error(sprintf('新增[%s]失败：%s',$item['ip'],$exception->getMessage()));
            }


        }
        $waring = $total - $successCount - $failCount;
        echo sprintf("成功 [\033[36m%s\033[0m] 失败 [\e[33m%s\e[0m] 失效 [\e[31m%s\e[0m]\n",$successCount,$failCount,$waring);
        return 0;
    }
}
