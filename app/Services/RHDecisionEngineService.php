<?php

namespace App\Services;

class RHDecisionEngineService
{
    public static function gerar(array $resumo, array $historico, array $previsoes)
    {
        $acoes = [];

        if (($resumo['resultado'] ?? 0) < 0) {
            $acoes[] = [
                'nivel' => 'critico',
                'titulo' => 'Revisar estrutura imediatamente',
                'acao' => 'Suspender contratações e revisar despesas fixas e variáveis.',
                'impacto' => 'Protege caixa no curto prazo.'
            ];
        }

        if (($resumo['peso_folha'] ?? 0) > 45) {
            $acoes[] = [
                'nivel' => 'critico',
                'titulo' => 'Folha acima do limite',
                'acao' => 'Reduzir pressão da folha antes de expandir equipe.',
                'impacto' => 'Melhora margem e reduz risco.'
            ];
        }

        if (!empty($previsoes)) {
            $ultimo = end($previsoes);
            if (($ultimo['margem'] ?? 0) < 5) {
                $acoes[] = [
                    'nivel' => 'alerta',
                    'titulo' => 'Margem futura apertada',
                    'acao' => 'Evitar aumentos lineares e reforçar receita antes de crescer.',
                    'impacto' => 'Previne queda de resultado em até 3 meses.'
                ];
            }

            if (($ultimo['resultado'] ?? 0) < 0) {
                $acoes[] = [
                    'nivel' => 'critico',
                    'titulo' => 'Prejuízo projetado',
                    'acao' => 'Ativar plano de contenção e foco comercial imediato.',
                    'impacto' => 'Pode evitar prejuízo futuro.'
                ];
            }
        }

        if (($resumo['margem'] ?? 0) >= 12 && ($resumo['peso_folha'] ?? 0) <= 30) {
            $acoes[] = [
                'nivel' => 'positivo',
                'titulo' => 'Janela de crescimento',
                'acao' => 'Cenário favorável para contratação planejada ou expansão.',
                'impacto' => 'Aproveita momento saudável.'
            ];
        }

        if (empty($acoes)) {
            $acoes[] = [
                'nivel' => 'positivo',
                'titulo' => 'Operação estável',
                'acao' => 'Manter acompanhamento mensal e crescimento controlado.',
                'impacto' => 'Preserva previsibilidade da operação.'
            ];
        }

        return $acoes;
    }
}
