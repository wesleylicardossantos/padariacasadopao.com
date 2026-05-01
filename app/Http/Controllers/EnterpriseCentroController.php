<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Funcionario;
use App\Modules\RH\Services\RHFolhaModuleService;
use App\Modules\RH\Support\ResolveEmpresaId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnterpriseCentroController extends Controller
{
    public function __construct(private RHFolhaModuleService $folha)
    {
    }

    public function index(Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $mes = (int) ($request->mes ?: date('m'));
        $ano = (int) ($request->ano ?: date('Y'));
        $resumo = $this->folha->montarResumoDetalhado($empresaId, $mes, $ano);

        $portalAtivos = Schema::hasTable('rh_portal_funcionarios')
            ? DB::table('rh_portal_funcionarios')->where('empresa_id', $empresaId)->where('ativo', 1)->count()
            : 0;
        $auditCount = Schema::hasTable('portal_audit_logs')
            ? DB::table('portal_audit_logs')->where('empresa_id', $empresaId)->whereDate('created_at', now()->toDateString())->count()
            : 0;
        $integracoesHoje = Schema::hasTable('integracao_logs')
            ? DB::table('integracao_logs')->where('empresa_id', $empresaId)->whereDate('created_at', now()->toDateString())->count()
            : 0;

        return view('enterprise.erp_center', [
            'mes' => $mes,
            'ano' => $ano,
            'empresaId' => $empresaId,
            'resumo' => $resumo['resumo'] ?? collect(),
            'folhaTotal' => $resumo['folhaTotal'] ?? 0,
            'totalSalarioBase' => $resumo['totalSalarioBase'] ?? 0,
            'totalEventos' => $resumo['totalEventos'] ?? 0,
            'totalDescontos' => $resumo['totalDescontos'] ?? 0,
            'resultadoAposFolha' => $resumo['resultadoAposFolha'] ?? 0,
            'resultadoCaixa' => $resumo['resultadoCaixa'] ?? 0,
            'pesoFolha' => $resumo['pesoFolha'] ?? 0,
            'capitalComprometido' => $resumo['capitalComprometido'] ?? 0,
            'coberturaFolha' => $resumo['coberturaFolha'] ?? 0,
            'alertasFinanceiros' => $resumo['alertasFinanceiros'] ?? [],
            'funcionariosAtivos' => Funcionario::where('empresa_id', $empresaId)->where('ativo', 1)->count(),
            'contasReceber' => ContaReceber::where('empresa_id', $empresaId)->count(),
            'contasPagar' => ContaPagar::where('empresa_id', $empresaId)->count(),
            'portalAtivos' => $portalAtivos,
            'auditCount' => $auditCount,
            'integracoesHoje' => $integracoesHoje,
        ]);
    }
}
