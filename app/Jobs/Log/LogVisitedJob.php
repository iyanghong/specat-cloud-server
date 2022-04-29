<?php

namespace App\Jobs\Log;

use App\Models\Log\LogVisited;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogVisitedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $visitor = [];

    /**
     * LogVisitedJob constructor.
     * @param array $visitor
     */
    public function __construct(array $visitor)
    {
        //
        $this->visitor = $visitor;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        /* @var $logVisitedModel Builder */
        $logVisitedModel = new LogVisited();
        $ipDetail = new \App\Service\Log\LogIpDetail();
        $ipDetail->setIp($this->visitor['ip']);
        $this->visitor['uuid'] = getUuid();
        $logVisitedModel->create($this->visitor);
    }

    /**
     * @return array
     */
    public function getVisitor(): array
    {
        return $this->visitor;
    }

    /**
     * @param array $visitor
     */
    public function setVisitor(array $visitor): void
    {
        $this->visitor = $visitor;
    }


}
