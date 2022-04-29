<?php


namespace App\Service\Log;


use App\Models\Log\LogVisited;
use App\Service\Statistic\Statistic;
use Illuminate\Database\Eloquent\Builder;

class LogVisits
{
    use StoreIp;

    private string $cacheKey = 'LogVisits';
    private \Redis $driver;

    public function __construct()
    {
        $this->driver = new \Redis();
    }

    /**
     * 存储缓存
     * Date : 2021/4/28 11:30
     * Author : 孤鸿渺影
     */
    public function cache()
    {
        $device = agent()->device();
        $platform = agent()->platform();
        $platformVersion = agent()->version($platform);
        $browser = agent()->browser();
        $browserVersion = agent()->version($browser);
        $origin = "";
        $device && $origin .= "$device;";
        $platform && $origin .= "$platform $platformVersion;";
        $browser && $origin .= "$browser $browserVersion;";
        $userUuid = '';
        if (onlineMember()->isLogin()) {
            $userUuid = onlineMember()->getUuid();
        }
        $data = [
            'os' => $device,
            'origin' => $origin,
            'entrance' => request()->input('entrance'),
            'router_name' => request()->input('router_name'),
            'user_uuid' => $userUuid,
            'ip' => request()->ip(),
            'time' => time(),
            'uuid' => getUuid()
        ];
        if (!empty(request()->input('type'))) {
            $data['type'] = request()->input('type');
        }
        $this->driver->lPush($this->cacheKey, $data);
        return true;
    }


    /**
     * 存储日志列表
     * Date : 2021/4/28 11:44
     * Author : 孤鸿渺影
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function store()
    {
        $list = $this->driver->lRange($this->cacheKey,0,-1);
        if(empty($list)){
            return;
        }
        $this->driver->del($this->cacheKey);
        /* @var $logVisitsModel Builder */
        $logVisitsModel = new LogVisited();
        foreach ($list as $item){
            $item =json_decode($item,true);
            if(!empty($item)){
                $this->setIp($item['ip']);
                $logVisitsModel->create($item);
            }
        }
        //刷新缓存
        (new Statistic())->cacheVisitedLineChart();
    }
}