<?php

namespace App\Jobs;

use App\Services\MercadoPago\MercadoPagoWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMercadoPagoWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $eventId)
    {
    }

    public function handle(MercadoPagoWebhookService $service): void
    {
        $service->processEvent($this->eventId);
    }

    public function failed(\Throwable $e): void
    {
        app(MercadoPagoWebhookService::class)->markFailed($this->eventId, $e);
    }
}
