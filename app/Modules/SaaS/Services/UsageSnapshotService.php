<?php

namespace App\Modules\SaaS\Services;

use App\Modules\SaaS\Models\SaasUsageSnapshot;
use Illuminate\Support\Facades\Schema;

class UsageSnapshotService
{
    public function snapshot(int $empresaId): ?SaasUsageSnapshot
    {
        if (! Schema::hasTable('saas_usage_snapshots')) {
            return null;
        }

        return SaasUsageSnapshot::query()->create([
            'empresa_id' => $empresaId,
            'reference_date' => now()->toDateString(),
            'usage_payload' => app(PlanLimitService::class)->limitsMatrix($empresaId),
        ]);
    }
}
