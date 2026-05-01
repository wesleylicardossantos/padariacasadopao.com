<?php

namespace App\Modules\BI\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BI\Services\DreService;
use App\Modules\BI\Services\KpiService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceController extends Controller
{
    use InteractsWithTenantContext;

    protected KpiService $kpis;
    protected DreService $dre;

    public function __construct(KpiService $kpis, DreService $dre)
    {
        $this->middleware('tenant.context');
        $this->kpis = $kpis;
        $this->dre = $dre;
    }

    public function index(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $filialId = $request->get('filial_id', 'todos');
        $ano = $request->integer('ano') ?: (int) date('Y');
        $mes = $request->integer('mes') ?: (int) date('n');
        $summary = $this->kpis->summary($empresaId, $filialId, $ano, $mes);
        $dre = $this->dre->summary($empresaId, $filialId, $ano, $mes);

        return view('enterprise.bi.index', [
            'empresa_id' => $empresaId,
            'filial_id' => $filialId,
            'ano' => $ano,
            'mes' => $mes,
            'summary' => $summary,
            'dre' => $dre,
            'updated_at' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);
        $filialId = $request->get('filial_id', 'todos');
        $ano = $request->integer('ano') ?: null;
        $mes = $request->integer('mes') ?: null;

        return response()->json([
            'success' => true,
            'module' => 'BI',
            'summary' => $this->kpis->summary($empresaId, $filialId, $ano, $mes),
        ]);
    }

    public function dre(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);
        $filialId = $request->get('filial_id', 'todos');

        return response()->json([
            'success' => true,
            'module' => 'BI',
            'dre' => $this->dre->summary($empresaId, $filialId, $request->integer('ano') ?: null, $request->integer('mes') ?: null),
        ]);
    }
}
