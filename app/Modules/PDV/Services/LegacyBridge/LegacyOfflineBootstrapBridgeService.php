<?php

namespace App\Modules\PDV\Services\LegacyBridge;

use App\Modules\PDV\Services\OfflineBootstrapService;
use App\Support\Tenancy\ResolveEmpresaId;
use Illuminate\Http\Request;

class LegacyOfflineBootstrapBridgeService
{
    public function __construct(private readonly OfflineBootstrapService $service)
    {
    }

    public function build(Request $request): array
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        if ($empresaId > 0 && !$request->has('empresa_id')) {
            $request->merge(['empresa_id' => $empresaId]);
        }

        return $this->service->build($request);
    }
}
