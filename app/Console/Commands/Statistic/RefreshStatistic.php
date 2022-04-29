<?php

namespace App\Console\Commands\Statistic;

use App\Service\Statistic\Statistic;
use Illuminate\Console\Command;

class RefreshStatistic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistic:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新统计缓存';

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
     * Date : 2021/4/28 14:57
     * Author : 孤鸿渺影
     * @return int
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        $statistic = new Statistic();
        $statistic->refresh([
            'blogStatisticTotalData',
            'visitedLineChart',
            'articleCategoryChart',
            'userProvinceChart',
            'newTotal'
        ]);
        return 0;
    }
}
