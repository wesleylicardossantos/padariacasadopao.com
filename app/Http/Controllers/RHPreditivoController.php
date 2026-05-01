<?php

namespace App\Http\Controllers;

use App\Services\RHAnalyticsService;
use App\Services\RHPredictiveService;
use Illuminate\Http\Request;

class RHPreditivoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;
        $mes = (int)($request->mes ?: date('m'));
        $ano = (int)($request->ano ?: date('Y'));

        $resumo = RHAnalyticsService::resumoCompetencia($empresaId, $mes, $ano);
        $dados = RHPredictiveService::gerar($empresaId, $mes, $ano, 6);

        return view('rh.preditivo_ia.index', compact('mes', 'ano', 'resumo', 'dados'));
    }
}
