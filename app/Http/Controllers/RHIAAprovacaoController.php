<?php

namespace App\Http\Controllers;

use App\Services\RHAnalyticsService;
use App\Services\RHActionApprovalService;
use Illuminate\Http\Request;

class RHIAAprovacaoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;
        $mes = (int)($request->mes ?: date('m'));
        $ano = (int)($request->ano ?: date('Y'));

        $resumo = RHAnalyticsService::resumoCompetencia($empresaId, $mes, $ano);
        $historico = RHAnalyticsService::historico($empresaId, $mes, $ano, 6);
        $sugestoes = RHActionApprovalService::sugestoes($resumo, $historico);

        return view('rh.ia_aprovacao.index', compact(
            'mes',
            'ano',
            'resumo',
            'historico',
            'sugestoes'
        ));
    }

    public function aprovarGet(Request $request)
    {
        return redirect('/rh/ia-aprovacao?mes=' . ($request->mes ?: date('m')) . '&ano=' . ($request->ano ?: date('Y')));
    }

    public function aprovar(Request $request)
    {
        $request->validate([
            'acao' => 'required|string|max:120',
        ]);

        $resultado = RHActionApprovalService::executar($request->acao, $request->all());

        if ($resultado['ok']) {
            session()->flash('flash_sucesso', $resultado['mensagem']);
        } else {
            session()->flash('flash_erro', $resultado['mensagem']);
        }

        return redirect('/rh/ia-aprovacao?mes=' . ($request->mes ?: date('m')) . '&ano=' . ($request->ano ?: date('Y')));
    }
}
