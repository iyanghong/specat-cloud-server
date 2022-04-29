<?php

namespace App\Jobs\Log;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IpDetail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $ip = '';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ip)
    {
        //
        $this->ip = $ip;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $ipDetail = new \App\Service\Log\LogIpDetail();
        $ipDetail->setIp($this->ip);
    }
}
