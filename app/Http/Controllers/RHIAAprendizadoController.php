<?php

namespace App\Http\Controllers;

use App\Services\RHAnalyticsService;
use App\Services\RHActionApprovalService;
use App\Services\RHIALearningService;
use Illuminate\Http\Request;

class RHIAAprendizadoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;
        $mes = (int)($request->mes ?: date('m'));
        $ano = (int)($request->ano ?: date('Y'));

        $resumo = RHAnalyticsService::resumoCompetencia($empresaId, $mes, $ano);
        $historico = RHAnalyticsService::historico($empresaId, $mes, $ano, 6);

        $sugestoes = RHActionApprovalService::sugestoes($resumo, $historico);
        $sugestoes = RHIALearningService::aplicarPrioridade($empresaId, $sugestoes);

        $memoria = RHIALearningService::resumoEmpresa($empresaId);
        $topAcoes = RHIALearningService::topAcoes($empresaId);

        return view('rh.ia_aprendizado.index', compact(
            'mes',
            'ano',
            'resumo',
            'historico',
            'sugestoes',
            'memoria',
            'topAcoes'
        ));
    }

    public function decidirGet(Request $request)
    {
        return redirect('/rh/ia-aprendizado?mes=' . ($request->mes ?: date('m')) . '&ano=' . ($request->ano ?: date('Y')));
    }

    public function decidir(Request $request)
    {
        $request->validate([
            'acao' => 'required|string|max:120',
            'titulo' => 'nullable|string|max:200',
            'decisao' => 'required|in:aprovado,rejeitado',
        ]);

        $empresaId = request()->empresa_id;

        RHIALearningService::registrar(
            $empresaId,
            $request->acao,
            $request->titulo,
            $request->decisao,
            $request->all()
        );

        if ($request->decisao === 'aprovado') {
            $resultado = RHActionApprovalService::executar($request->acao, $request->all());

            if (!empty($resultado['ok'])) {
                session()->flash('flash_sucesso', 'Decisão registrada e ação executada.');
            } else {
                session()->flash('flash_erro', $resultado['mensagem'] ?? 'Falha ao executar ação aprovada.');
            }
        } else {
            session()->flash('flash_sucesso', 'Decisão registrada como rejeitada. A IA vai aprender com isso.');
        }

        return redirect('/rh/ia-aprendizado?mes=' . ($request->mes ?: date('m')) . '&ano=' . ($request->ano ?: date('Y')));
    }
}
