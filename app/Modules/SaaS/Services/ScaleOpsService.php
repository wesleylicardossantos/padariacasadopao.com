<?php

namespace App\Modules\SaaS\Services;

use App\Support\Cache\TenantCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ScaleOpsService
{
    public function snapshot(int $empresaId): array
    {
        return TenantCache::remember('saas-scale', $empresaId, 'snapshot', now()->addMinutes(5), function () use ($empresaId) {
            $jobsPending = Schema::hasTable('jobs') ? (int) DB::table('jobs')->count() : 0;
            $failedJobs = Schema::hasTable('failed_jobs') ? (int) DB::table('failed_jobs')->count() : 0;
            $cacheRows = Schema::hasTable('cache') ? (int) DB::table('cache')->count() : 0;
            $notifications = Schema::hasTable('saas_premium_notifications')
                ? (int) DB::table('saas_premium_notifications')->where('empresa_id', $empresaId)->count()
                : 0;
            $usageSnapshots = Schema::hasTable('saas_usage_snapshots')
                ? (int) DB::table('saas_usage_snapshots')->where('empresa_id', $empresaId)->count()
                : 0;
            $pdvPending = Schema::hasTable('pdv_offline_syncs')
                ? (int) DB::table('pdv_offline_syncs')->where('empresa_id', $empresaId)->where('status', '!=', 'sincronizado')->count()
                : 0;

            return [
                'empresa_id' => $empresaId,
                'queue_connection' => (string) config('queue.default'),
                'cache_driver' => (string) config('cache.default'),
                'jobs_pending' => $jobsPending,
                'failed_jobs' => $failedJobs,
                'cache_rows' => $cacheRows,
                'notifications' => $notifications,
                'usage_snapshots' => $usageSnapshots,
                'pdv_pending_sync' => $pdvPending,
                'healthy' => $jobsPending < 1000 && $failedJobs < 100 && $pdvPending < 100,
            ];
        });
    }
}
