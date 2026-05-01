<?php

namespace App\Http\Controllers;

use App\Services\RHAnalyticsService;
use App\Services\RHMachineLearningService;
use App\Services\RHDecisionEngineService;
use Illuminate\Http\Request;

class RHModoAbsurdoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;
        $mes = (int)($request->mes ?: date('m'));
        $ano = (int)($request->ano ?: date('Y'));

        $resumo = RHAnalyticsService::resumoCompetencia($empresaId, $mes, $ano);
        $historico = RHAnalyticsService::historico($empresaId, $mes, $ano, 6);
        $aprendizado = RHMachineLearningService::analisarHistorico($historico);
        $previsoes = RHMachineLearningService::preverProximosMeses($historico, 3);
        $score = RHAnalyticsService::score($resumo);
        $alertas = RHAnalyticsService::alertas($resumo);
        $acoes = RHDecisionEngineService::gerar($resumo, $historico, $previsoes);
        $setores = RHAnalyticsService::custosPorSetor($empresaId);

        return view('rh.modo_absurdo.index', compact(
            'mes',
            'ano',
            'resumo',
            'historico',
            'aprendizado',
            'previsoes',
            'score',
            'alertas',
            'acoes',
            'setores'
        ));
    }
}
