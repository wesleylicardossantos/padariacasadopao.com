<?php

namespace App\Modules\SaaS\Controllers\API;

use App\Modules\SaaS\Services\InternalApiService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class InternalSaasController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(private InternalApiService $service)
    {
        $this->middleware(['tenant.context', 'throttle:enterprise']);
    }

    public function executive(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);
        return response()->json(['ok' => true, 'data' => $this->service->executivePayload($empresaId)]);
    }

    public function premium(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);
        return response()->json(['ok' => true, 'data' => $this->service->premiumPayload($empresaId)]);
    }

    public function scale(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);
        return response()->json(['ok' => true, 'data' => $this->service->scalePayload($empresaId)]);
    }
}
