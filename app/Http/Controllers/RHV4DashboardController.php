<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\RHFerias;
use App\Models\RHFalta;
use App\Models\RHDesligamento;
use Illuminate\Support\Facades\Schema;

class RHV4DashboardController extends Controller
{
    public function index()
    {
        $empresaId = request()->empresa_id;

        $totalFuncionarios = Funcionario::where('empresa_id', $empresaId)->count();
        $ativos = Funcionario::where('empresa_id', $empresaId)
            ->where(function($q){
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })->count();

        $desligamentosMes = Schema::hasTable('rh_desligamentos')
            ? RHDesligamento::where('empresa_id', $empresaId)
                ->whereMonth('data_desligamento', date('m'))
                ->whereYear('data_desligamento', date('Y'))
                ->count()
            : 0;

        $admissoesMes = 0;
        if (Schema::hasTable('funcionarios_ficha_admissao')) {
            $admissoesMes = \App\Models\FuncionarioFichaAdmissao::where('empresa_id', $empresaId)
                ->whereMonth('data_admissao', date('m'))
                ->whereYear('data_admissao', date('Y'))
                ->count();
        }

        $turnover = 0;
        if ($ativos > 0) {
            $turnover = (($admissoesMes + $desligamentosMes) / max($ativos, 1)) * 100;
        }

        $faltasMes = Schema::hasTable('rh_faltas')
            ? RHFalta::where('empresa_id', $empresaId)
                ->where('tipo', 'falta')
                ->whereMonth('data_referencia', date('m'))
                ->whereYear('data_referencia', date('Y'))
                ->count()
            : 0;

        $atestadosMes = Schema::hasTable('rh_faltas')
            ? RHFalta::where('empresa_id', $empresaId)
                ->where('tipo', 'atestado')
                ->whereMonth('data_referencia', date('m'))
                ->whereYear('data_referencia', date('Y'))
                ->count()
            : 0;

        $atrasosMes = Schema::hasTable('rh_faltas')
            ? RHFalta::where('empresa_id', $empresaId)
                ->where('tipo', 'atraso')
                ->whereMonth('data_referencia', date('m'))
                ->whereYear('data_referencia', date('Y'))
                ->count()
            : 0;

        $folhaBase = Funcionario::where('empresa_id', $empresaId)
            ->where(function($q){
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })
            ->sum('salario');

        $feriasProximas = collect();
        if (Schema::hasTable('rh_ferias')) {
            $feriasProximas = RHFerias::with('funcionario')
                ->where('empresa_id', $empresaId)
                ->whereIn('status', ['programada', 'pendente'])
                ->orderBy('data_inicio', 'asc')
                ->limit(10)
                ->get();
        }

        return view('rh.v4.dashboard', compact(
            'totalFuncionarios',
            'ativos',
            'desligamentosMes',
            'admissoesMes',
            'turnover',
            'faltasMes',
            'atestadosMes',
            'atrasosMes',
            'folhaBase',
            'feriasProximas'
        ));
    }
}
