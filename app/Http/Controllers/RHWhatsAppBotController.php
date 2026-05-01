<?php

namespace App\Http\Controllers;

use App\Services\RHAnalyticsService;
use App\Services\RHMachineLearningService;
use Illuminate\Http\Request;

class RHWhatsAppBotController extends Controller
{
    public function index()
    {
        return view('rh.whatsapp_bot.index', [
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
        $aprendizado = RHMachineLearningService::analisarHistorico($historico);

        $pergunta = mb_strtolower(trim($request->pergunta));
        $resposta = $this->resolverPergunta($pergunta, $resumo, $aprendizado);

        return view('rh.whatsapp_bot.index', compact('pergunta', 'resposta'));
    }

    private function resolverPergunta($pergunta, array $resumo, array $aprendizado)
    {
        if (str_contains($pergunta, 'como está minha empresa') || str_contains($pergunta, 'como esta minha empresa')) {
            return 'Score geral em monitoramento. Receita: R$ ' . number_format($resumo['receita'], 2, ',', '.') .
                ', RH: R$ ' . number_format($resumo['rh'], 2, ',', '.') .
                ', Resultado: R$ ' . number_format($resumo['resultado'], 2, ',', '.') . '.';
        }

        if (str_contains($pergunta, 'posso contratar')) {
            if (($resumo['resultado'] ?? 0) < 0 || ($resumo['peso_folha'] ?? 0) > 45) {
                return 'Não é recomendado contratar agora. O cenário mostra pressão na folha ou resultado negativo.';
            }
            if (($resumo['margem'] ?? 0) < 5) {
                return 'Contratação com risco. A margem atual está apertada.';
            }
            return 'Há espaço para contratação planejada no cenário atual.';
        }

        if (str_contains($pergunta, 'qual setor tá caro') || str_contains($pergunta, 'qual setor ta caro')) {
            $setores = RHAnalyticsService::custosPorSetor(request()->empresa_id);
            if (!empty($setores)) {
                return 'Setor/função com maior custo atual: ' . $setores[0]['setor'] .
                    ' (R$ ' . number_format($setores[0]['custo'], 2, ',', '.') . ').';
            }
            return 'Não encontrei dados suficientes para identificar o setor mais caro.';
        }

        if (($aprendizado['tendencia_resultado'] ?? 'estavel') === 'queda') {
            return 'A tendência recente do resultado é de queda. Recomendo foco em produtividade, receita e contenção da folha.';
        }

        return 'Pergunta recebida. Reformule com foco em contratação, custo, setor ou saúde da empresa.';
    }
}
