<?php

namespace App\Modules\Fiscal\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fiscal\Services\FiscalOperationsReportService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(
        private readonly FiscalOperationsReportService $reportService,
    ) {
        $this->middleware('tenant.context');
    }

    public function snapshot(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);

        return response()->json([
            'success' => true,
            'module' => 'Fiscal',
            'data' => $this->reportService->summary($empresaId),
        ]);
    }
}
