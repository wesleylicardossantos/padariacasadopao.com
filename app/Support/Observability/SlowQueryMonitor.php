<?php

namespace App\Support\Observability;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SlowQueryMonitor
{
    private static bool $writingDatabaseEvent = false;

    public function handle(QueryExecuted $query): void
    {
        if (!config('infra.slow_query.enabled', true) || self::$writingDatabaseEvent) {
            return;
        }

        $threshold = max(1, (int) config('infra.slow_query.threshold_ms', 350));
        if ($query->time < $threshold) {
            return;
        }

        $sampleRate = max(1, (int) config('infra.slow_query.sample_rate', 1));
        if ($sampleRate > 1 && random_int(1, $sampleRate) !== 1) {
            return;
        }

        $sql = $query->sql;
        $maxSqlLength = max(200, (int) config('infra.slow_query.max_sql_length', 4000));
        if (mb_strlen($sql) > $maxSqlLength) {
            $sql = mb_substr($sql, 0, $maxSqlLength) . '...';
        }

        $payload = [
            'empresa_id' => (int) (request()?->get('empresa_id') ?: data_get(session('user_logged'), 'empresa', 0)),
            'path' => request()?->path(),
            'method' => request()?->method(),
            'connection' => $query->connectionName,
            'time_ms' => round((float) $query->time, 2),
            'sql' => $sql,
            'bindings_count' => count($query->bindings),
            'bindings_preview' => array_slice(array_map(static fn ($binding) => is_scalar($binding) || $binding === null ? $binding : gettype($binding), $query->bindings), 0, 10),
            'occurred_at' => now()->toIso8601String(),
        ];

        Log::channel(config('finance_governance.log_channel', config('logging.default')))
            ->warning('Slow query detected in enterprise runtime.', $payload);

        if (!config('infra.slow_query.store_database_events', true)) {
            return;
        }

        try {
            if (!Schema::hasTable('performance_events')) {
                return;
            }

            self::$writingDatabaseEvent = true;
            DB::table('performance_events')->insert([
                'empresa_id' => $payload['empresa_id'] ?: null,
                'event_type' => 'slow_query',
                'context' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable) {
            // observabilidade não deve quebrar request produtiva
        } finally {
            self::$writingDatabaseEvent = false;
        }
    }
}
