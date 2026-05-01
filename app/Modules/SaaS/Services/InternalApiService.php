<?php

namespace App\Modules\SaaS\Services;

use App\Support\Cache\TenantCache;

class InternalApiService
{
    public function executivePayload(int $empresaId): array
    {
        return TenantCache::remember('internal-api', $empresaId, 'executive', now()->addMinutes(3), function () use ($empresaId) {
            return app(ExecutiveDashboardService::class)->build($empresaId);
        });
    }

    public function premiumPayload(int $empresaId): array
    {
        return TenantCache::remember('internal-api', $empresaId, 'premium', now()->addMinutes(3), function () use ($empresaId) {
            return app(PremiumAnalyticsService::class)->build($empresaId);
        });
    }

    public function scalePayload(int $empresaId): array
    {
        return TenantCache::remember('internal-api', $empresaId, 'scale', now()->addMinutes(3), function () use ($empresaId) {
            return app(ScaleOpsService::class)->snapshot($empresaId);
        });
    }
}
