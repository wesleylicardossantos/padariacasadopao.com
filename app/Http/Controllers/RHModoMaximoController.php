<?php

namespace App\Http\Controllers;

use App\Services\RHAnalyticsService;
use Illuminate\Http\Request;

class RHModoMaximoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;
        $mes = (int)($request->mes ?: date('m'));
        $ano = (int)($request->ano ?: date('Y'));

        $resumo = RHAnalyticsService::resumoCompetencia($empresaId, $mes, $ano);
        $historico = RHAnalyticsService::historico($empresaId, $mes, $ano, 6);
        $tendencia = RHAnalyticsService::tendenciaHistorica($historico);
        $score = RHAnalyticsService::score($resumo);
        $alertas = RHAnalyticsService::alertas($resumo);
        $acoes = RHAnalyticsService::recomendacoesAutonomas($resumo, $historico);
        $previsoes = RHAnalyticsService::previsaoTresMeses($historico);
        $setores = RHAnalyticsService::custosPorSetor($empresaId);

        $modoDono = [
            'status' => $score >= 80 ? 'excelente' : ($score >= 60 ? 'atencao' : 'risco'),
            'decisao' => $score < 60
                ? 'Segurar custos e evitar expandir equipe agora.'
                : ($score < 80
                    ? 'Crescimento controlado, com foco em produtividade.'
                    : 'Cenário favorável para crescimento planejado.')
        ];

        return view('rh.modo_maximo.index', compact(
            'mes',
            'ano',
            'resumo',
            'historico',
            'tendencia',
            'score',
            'alertas',
            'acoes',
            'previsoes',
            'setores',
            'modoDono'
        ));
    }
}
