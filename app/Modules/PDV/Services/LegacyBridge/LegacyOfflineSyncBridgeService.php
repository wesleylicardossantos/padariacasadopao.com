<?php

namespace App\Modules\PDV\Services\LegacyBridge;

use App\Modules\PDV\Services\OfflineSaleSyncService;
use App\Support\Tenancy\ResolveEmpresaId;
use Illuminate\Http\Request;

class LegacyOfflineSyncBridgeService
{
    public function __construct(private readonly OfflineSaleSyncService $service)
    {
    }

    public function sync(Request $request): array
    {
        $this->mergeTenantContext($request);

        return $this->service->sync($request);
    }

    public function status(Request $request): array
    {
        $this->mergeTenantContext($request);

        return $this->service->status($request);
    }

    public function dashboard(Request $request): array
    {
        $this->mergeTenantContext($request);

        return $this->service->dashboard($request);
    }

    private function mergeTenantContext(Request $request): void
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        if ($empresaId > 0 && !$request->has('empresa_id')) {
            $request->merge(['empresa_id' => $empresaId]);
        }
    }
}
