<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMercadoPagoWebhookJob;
use App\Services\MercadoPago\MercadoPagoWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        try {
            $service = app(MercadoPagoWebhookService::class);
            $event = $service->storeIncomingEvent($request);

            if ($event->processed_at) {
                return response()->json(['ok' => true, 'message' => 'already_processed'], 200);
            }

            $shouldQueue = config('queue.default') === 'database' && Schema::hasTable('jobs');

            if ($shouldQueue) {
                ProcessMercadoPagoWebhookJob::dispatch((int) $event->id);
            } else {
                $service->processEvent((int) $event->id);
            }

            return response()->json(['ok' => true], 200);
        } catch (\Throwable $e) {
            Log::error('Falha ao receber webhook Mercado Pago', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json(['ok' => false, 'message' => 'webhook_error'], 500);
        }
    }
}
