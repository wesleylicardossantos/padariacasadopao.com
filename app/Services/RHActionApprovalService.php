<?php

namespace App\Services;

class RHActionApprovalService
{
    public static function sugestoes(array $resumo, array $historico = [])
    {
        $sugestoes = [];

        if (($resumo['resultado'] ?? 0) < 0) {
            $sugestoes[] = [
                'acao' => 'bloquear_novas_contratacoes',
                'titulo' => 'Bloquear novas contratações',
                'descricao' => 'Recomendado porque o resultado operacional está negativo.',
                'risco' => 'baixo',
            ];
        }

        if (($resumo['peso_folha'] ?? 0) > 45) {
            $sugestoes[] = [
                'acao' => 'emitir_alerta_folha',
                'titulo' => 'Emitir alerta de folha alta',
                'descricao' => 'Folha acima de 45% do faturamento.',
                'risco' => 'baixo',
            ];
        }

        if (($resumo['margem'] ?? 0) < 5) {
            $sugestoes[] = [
                'acao' => 'gerar_relatorio_contencao',
                'titulo' => 'Gerar relatório de contenção',
                'descricao' => 'Margem operacional abaixo de 5%.',
                'risco' => 'baixo',
            ];
        }

        if (empty($sugestoes)) {
            $sugestoes[] = [
                'acao' => 'nenhuma_acao_critica',
                'titulo' => 'Sem ação crítica',
                'descricao' => 'Cenário estável no momento.',
                'risco' => 'baixo',
            ];
        }

        return $sugestoes;
    }

    public static function executar($acao, array $contexto = [])
    {
        switch ($acao) {
            case 'bloquear_novas_contratacoes':
                return [
                    'ok' => true,
                    'mensagem' => 'Ação aprovada: política de bloqueio de novas contratações registrada.',
                ];

            case 'emitir_alerta_folha':
                return [
                    'ok' => true,
                    'mensagem' => 'Ação aprovada: alerta de folha alta registrado.',
                ];

            case 'gerar_relatorio_contencao':
                return [
                    'ok' => true,
                    'mensagem' => 'Ação aprovada: relatório de contenção solicitado.',
                ];

            case 'nenhuma_acao_critica':
                return [
                    'ok' => true,
                    'mensagem' => 'Nenhuma execução necessária.',
                ];

            default:
                return [
                    'ok' => false,
                    'mensagem' => 'Ação não reconhecida.',
                ];
        }
    }
}
