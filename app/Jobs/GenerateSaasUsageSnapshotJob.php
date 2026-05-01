<?php

namespace App\Jobs;

use App\Modules\SaaS\Services\UsageSnapshotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSaasUsageSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $empresaId)
    {
        $this->onQueue('saas');
    }

    public function handle(UsageSnapshotService $service): void
    {
        $service->capture($this->empresaId);
    }
}
