<?php

namespace App\Services;

class RHPredictiveService
{
    public static function gerar($empresaId, $mes, $ano, $janela = 6)
    {
        $historico = RHAnalyticsService::historico($empresaId, $mes, $ano, $janela);
        $aprendizado = RHMachineLearningService::analisarHistorico($historico);
        $projecoes = RHMachineLearningService::preverProximosMeses($historico, 3);

        $risco = 'baixo';
        foreach ($projecoes as $p) {
            if (($p['resultado'] ?? 0) < 0 || ($p['margem'] ?? 0) < 5) {
                $risco = 'alto';
                break;
            }
            if (($p['margem'] ?? 0) < 10) {
                $risco = 'medio';
            }
        }

        $parecer = self::parecer($aprendizado, $projecoes);

        return [
            'historico' => $historico,
            'aprendizado' => $aprendizado,
            'projecoes' => $projecoes,
            'risco' => $risco,
            'parecer' => $parecer,
        ];
    }

    private static function parecer(array $aprendizado, array $projecoes)
    {
        $ultimo = end($projecoes);
        if (($ultimo['resultado'] ?? 0) < 0) {
            return 'Tendência de prejuízo projetado. Recomendado conter custos e evitar expansão.';
        }
        if (($ultimo['margem'] ?? 0) < 5) {
            return 'Tendência de margem apertada. Recomendado priorizar receita e produtividade.';
        }
        if (($aprendizado['tendencia_resultado'] ?? 'estavel') === 'queda') {
            return 'Resultado em queda. Monitorar crescimento da folha e reforçar operação comercial.';
        }
        return 'Cenário preditivo estável no curto prazo.';
    }
}
