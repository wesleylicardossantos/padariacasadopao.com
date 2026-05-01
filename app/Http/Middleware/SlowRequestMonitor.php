<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SlowRequestMonitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        /** @var Response $response */
        $response = $next($request);

        if (!config('infra.slow_request.enabled', true)) {
            return $response;
        }

        $elapsedMs = round((microtime(true) - $startedAt) * 1000, 2);
        $threshold = max(1, (int) config('infra.slow_request.threshold_ms', 1200));
        if ($elapsedMs < $threshold) {
            return $response;
        }

        $sampleRate = max(1, (int) config('infra.slow_request.sample_rate', 1));
        if ($sampleRate > 1 && random_int(1, $sampleRate) !== 1) {
            return $response;
        }

        $payload = [
            'empresa_id' => (int) ($request->get('empresa_id') ?: data_get(session('user_logged'), 'empresa', 0)),
            'path' => $request->path(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $elapsedMs,
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
            'ip' => $request->ip(),
            'occurred_at' => now()->toIso8601String(),
        ];

        Log::channel(config('finance_governance.log_channel', config('logging.default')))
            ->warning('Slow request detected in enterprise runtime.', $payload);

        if (!config('infra.slow_request.store_database_events', true)) {
            return $response;
        }

        try {
            if (!Schema::hasTable('performance_events')) {
                return $response;
            }

            DB::table('performance_events')->insert([
                'empresa_id' => $payload['empresa_id'] ?: null,
                'event_type' => 'slow_request',
                'context' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable) {
            // observabilidade nunca deve quebrar a request principal
        }

        return $response;
    }
}
