<?php

namespace App\Modules\Financeiro\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financeiro\Services\CashFlowService;
use App\Modules\Financeiro\Services\FinancialAuditService;
use App\Modules\Financeiro\Services\FinancialMetricsService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(
        protected FinancialMetricsService $metrics,
        protected CashFlowService $cashFlow,
        protected FinancialAuditService $audit,
    ) {
        $this->middleware('tenant.context');
    }

    public function index(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $filialId = $request->get('filial_id', 'todos');
        $snapshot = $this->metrics->snapshot($empresaId, $filialId);
        $aging = $this->metrics->aging($empresaId, $filialId);
        $overview = $this->metrics->overview($empresaId, $filialId);
        $cashFlow = $this->cashFlow->projection($empresaId, $filialId, 6);

        return view('enterprise.financeiro.index', [
            'empresa_id' => $empresaId,
            'filial_id' => $filialId,
            'snapshot' => $snapshot,
            'aging' => $aging,
            'overview' => $overview,
            'cash_flow' => $cashFlow,
            'updated_at' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    public function kpis(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);
        $filialId = $request->get('filial_id', 'todos');

        return response()->json([
            'success' => true,
            'module' => 'Financeiro',
            'snapshot' => $this->metrics->snapshot($empresaId, $filialId),
            'aging' => $this->metrics->aging($empresaId, $filialId),
            'cash_flow' => $this->cashFlow->projection($empresaId, $filialId, 6),
        ]);
    }

    public function audit(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $filialId = $request->get('filial_id', 'todos');
        $audit = $this->audit->validate($empresaId, $filialId);

        return view('enterprise.financeiro.audit', [
            'empresa_id' => $empresaId,
            'filial_id' => $filialId,
            'audit' => $audit,
        ]);
    }

    public function inconsistencias(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);
        $filialId = $request->get('filial_id', 'todos');

        return response()->json([
            'success' => true,
            'module' => 'Financeiro',
            'audit' => $this->audit->validate($empresaId, $filialId),
        ]);
    }
}
