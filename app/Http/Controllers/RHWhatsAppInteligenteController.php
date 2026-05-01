<?php

namespace App\Http\Controllers;

use App\Services\RHAnalyticsService;
use Illuminate\Http\Request;

class RHWhatsAppInteligenteController extends Controller
{
    public function index(Request $request)
    {
        return view('rh.whatsapp_inteligente.index', [
            'pergunta' => '',
            'resposta' => null,
        ]);
    }

    public function responder(Request $request)
    {
        $request->validate([
            'pergunta' => 'required|string|max:500',
        ]);

        $empresaId = request()->empresa_id;
        $mes = (int)date('m');
        $ano = (int)date('Y');

        $resumo = RHAnalyticsService::resumoCompetencia($empresaId, $mes, $ano);
        $historico = RHAnalyticsService::historico($empresaId, $mes, $ano, 6);
        $score = RHAnalyticsService::score($resumo);

        $pergunta = mb_strtolower(trim($request->pergunta));
        $resposta = $this->montarResposta($pergunta, $resumo, $historico, $score);

        return view('rh.whatsapp_inteligente.index', compact('pergunta', 'resposta'));
    }

    private function montarResposta($pergunta, array $resumo, array $historico, $score)
    {
        if (str_contains($pergunta, 'como está minha empresa') || str_contains($pergunta, 'como esta minha empresa')) {
            return "Score atual: {$score}/100. Receita: R$ " . number_format($resumo['receita'], 2, ',', '.') .
                ". RH: R$ " . number_format($resumo['rh'], 2, ',', '.') .
                ". Resultado: R$ " . number_format($resumo['resultado'], 2, ',', '.') . ".";
        }

        if (str_contains($pergunta, 'posso contratar')) {
            if ($resumo['resultado'] < 0 || $resumo['peso_folha'] > 45) {
                return 'Não é recomendado contratar agora. O cenário atual mostra pressão na folha ou resultado negativo.';
            }

            if ($resumo['margem'] < 5) {
                return 'Contratação com risco. A margem atual está apertada.';
            }

            return 'Há espaço para contratação planejada no cenário atual.';
        }

        if (str_contains($pergunta, 'quanto posso pagar') || str_contains($pergunta, 'salário')) {
            $folga = max(0, $resumo['resultado'] * 0.30);
            return 'Faixa conservadora para nova contratação: até R$ ' . number_format($folga, 2, ',', '.') .
                ' de custo total mensal, considerando 30% da folga operacional.';
        }

        if (str_contains($pergunta, 'qual setor tá caro') || str_contains($pergunta, 'qual setor ta caro')) {
            $setores = RHAnalyticsService::custosPorSetor(request()->empresa_id);
            if (!empty($setores)) {
                return 'Setor/função com maior custo atual: ' . $setores[0]['setor'] .
                    ' (R$ ' . number_format($setores[0]['custo'], 2, ',', '.') . ').';
            }
            return 'Não encontrei dados suficientes para identificar o setor mais caro.';
        }

        $ultimo = end($historico);
        $primeiro = reset($historico);
        if (($ultimo['resultado'] ?? 0) < ($primeiro['resultado'] ?? 0)) {
            return 'Tendência recente de queda no resultado. Recomendo foco em produtividade, receita e contenção da folha.';
        }

        return 'Pergunta recebida. Cenário atual está sob monitoramento. Reformule com foco em contratação, custo, empresa ou setor.';
    }
}
