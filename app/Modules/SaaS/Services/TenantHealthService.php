<?php

namespace App\Modules\SaaS\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantHealthService
{
    public function health(int $empresaId): array
    {
        $checks = [
            'empresa_id' => $empresaId,
            'jobs_table' => Schema::hasTable('jobs'),
            'cache_store' => config('cache.default'),
            'queue_connection' => config('queue.default'),
            'pdv_sync_table' => Schema::hasTable('pdv_offline_syncs'),
            'saas_snapshot_table' => Schema::hasTable('saas_usage_snapshots'),
        ];

        $checks['jobs_pending'] = $checks['jobs_table'] ? (int) DB::table('jobs')->count() : 0;
        $checks['pdv_pending_sync'] = $checks['pdv_sync_table'] ? (int) DB::table('pdv_offline_syncs')->where('empresa_id', $empresaId)->where('status', '!=', 'sincronizado')->count() : 0;
        $checks['healthy'] = $checks['jobs_pending'] < 1000 && $checks['pdv_pending_sync'] < 100;

        return $checks;
    }
}
