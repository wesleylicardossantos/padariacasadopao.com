<?php

namespace App\Modules\Comercial\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comercial\Services\SalesMetricsService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(private readonly SalesMetricsService $metrics)
    {
        $this->middleware('tenant.context');
    }

    public function kpis(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);
        $filialId = $request->get('filial_id', 'todos');

        return response()->json([
            'success' => true,
            'module' => 'Comercial',
            'today' => $this->metrics->today($empresaId, $filialId),
            'month' => $this->metrics->currentMonth($empresaId, $filialId),
        ]);
    }
}
