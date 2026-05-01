<?php

namespace App\Modules\SaaS\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ObservabilityService
{
    public function summary(int $empresaId): array
    {
        $items = [];
        if (Schema::hasTable('rh_admin_action_audits')) {
            $items['rh_admin_audits'] = (int) DB::table('rh_admin_action_audits')->where('empresa_id', $empresaId)->count();
        }
        if (Schema::hasTable('saas_premium_notifications')) {
            $items['premium_notifications'] = (int) DB::table('saas_premium_notifications')->where('empresa_id', $empresaId)->count();
        }
        if (Schema::hasTable('stock_write_audits')) {
            $items['stock_guard_events'] = (int) DB::table('stock_write_audits')->where('empresa_id', $empresaId)->count();
        }
        $items['queue_connection'] = (string) config('queue.default');
        $items['cache_driver'] = (string) config('cache.default');
        $items['logs_channel'] = (string) config('logging.default');
        $items['tenant_runtime_bound'] = app()->bound('tenant.empresa_id');

        return $items;
    }
}
