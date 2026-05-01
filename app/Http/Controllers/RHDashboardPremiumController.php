<?php

namespace App\Http\Controllers;

use App\Services\RHAnalyticsService;
use Illuminate\Http\Request;

class RHDashboardPremiumController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;
        $mes = (int)($request->mes ?: date('m'));
        $ano = (int)($request->ano ?: date('Y'));

        $resumo = RHAnalyticsService::resumoCompetencia($empresaId, $mes, $ano);
        $historico = RHAnalyticsService::historico($empresaId, $mes, $ano, 6);
        $score = RHAnalyticsService::score($resumo);
        $alertas = RHAnalyticsService::alertas($resumo);
        $previsoes = RHAnalyticsService::previsaoTresMeses($historico);
        $setores = RHAnalyticsService::custosPorSetor($empresaId);

        return view('rh.dashboard_premium.index', compact(
            'mes',
            'ano',
            'resumo',
            'historico',
            'score',
            'alertas',
            'previsoes',
            'setores'
        ));
    }
}
