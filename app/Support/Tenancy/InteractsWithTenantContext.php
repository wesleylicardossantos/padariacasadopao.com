<?php

namespace App\Support\Tenancy;

use Illuminate\Http\Request;

trait InteractsWithTenantContext
{
    protected function tenantEmpresaId(?Request $request = null, int $fallback = 0): int
    {
        return TenantContext::empresaId($request, $fallback);
    }

    protected function tenantFilialId(?Request $request = null, ?int $fallback = null): ?int
    {
        return TenantContext::filialId($request, $fallback);
    }

    protected function tenantUserId(?Request $request = null, ?int $fallback = null): ?int
    {
        return TenantContext::userId($request, $fallback);
    }

    protected function tenantSnapshot(?Request $request = null): array
    {
        return TenantContext::snapshot($request);
    }
}
