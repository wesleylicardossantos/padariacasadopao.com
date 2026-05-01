<?php

namespace App\Modules\RH\Services;

class RHOwnerDashboardService
{
    public function __construct(
        private RHDecisionEngineService $decisionEngine,
        private RHFinanceiroIntegrationService $financeiro,
        private RHAnalyticsModuleService $analytics
    ) {
    }

    public function montar(int $empresaId, int $mes, int $ano): array
    {
        $ia = $this->decisionEngine->analisar($empresaId, $mes, $ano);
        $financeiro = $this->financeiro->competencia($empresaId, $mes, $ano);
        $serie = $this->financeiro->serieMensal($empresaId, $mes, $ano, 6);
        $dre = $this->analytics->montarDreInteligente($empresaId, $mes, $ano);

        $receita = (float) ($financeiro['receitaPrevista'] ?? 0);
        $recebido = (float) ($financeiro['receitaRecebida'] ?? 0);
        $despesa = (float) ($financeiro['despesaPrevista'] ?? 0);
        $pago = (float) ($financeiro['despesaPaga'] ?? 0);
        $rh = (float) ($financeiro['custosRh']['total_rh'] ?? 0);
        $folhaLiquida = (float) ($financeiro['custosRh']['folha_liquida'] ?? 0);
        $lucro = (float) ($ia['resultado'] ?? 0);
        $caixa = $recebido - $pago;
        $margem = $receita > 0 ? ($lucro / $receita) * 100 : 0;
        $receitaConvertida = $receita > 0 ? ($recebido / $receita) * 100 : 0;
        $despesaPagaRatio = $despesa > 0 ? ($pago / $despesa) * 100 : 0;

        $insights = [];
        $insights[] = [
            'titulo' => 'Saúde da operação',
            'valor' => round((float) ($ia['scoreSaude'] ?? 0), 2),
            'sufixo' => '/100',
            'status' => $ia['statusSaude'] ?? 'atencao',
            'descricao' => 'Score consolidado de receita, caixa, peso da folha, capital comprometido e tendência recente.',
        ];
        $insights[] = [
            'titulo' => 'Margem operacional',
            'valor' => round($margem, 2),
            'sufixo' => '%',
            'status' => $margem >= 12 ? 'saudavel' : ($margem >= 5 ? 'atencao' : 'critico'),
            'descricao' => 'Margem depois do custo total de RH e demais despesas previstas.',
        ];
        $insights[] = [
            'titulo' => 'Conversão de receita em caixa',
            'valor' => round($receitaConvertida, 2),
            'sufixo' => '%',
            'status' => $receitaConvertida >= 85 ? 'saudavel' : ($receitaConvertida >= 70 ? 'atencao' : 'critico'),
            'descricao' => 'Quanto da receita prevista realmente virou caixa no período.',
        ];
        $insights[] = [
            'titulo' => 'Cobertura da folha',
            'valor' => round((float) ($financeiro['coberturaFolha'] ?? 0), 2),
            'sufixo' => 'x',
            'status' => ($financeiro['coberturaFolha'] ?? 0) >= 1.5 ? 'saudavel' : (($financeiro['coberturaFolha'] ?? 0) >= 1 ? 'atencao' : 'critico'),
            'descricao' => 'Quantas vezes a receita recebida cobre a folha líquida do mês.',
        ];

        $placar = [
            'receita' => round($receita, 2),
            'recebido' => round($recebido, 2),
            'despesa' => round($despesa, 2),
            'pago' => round($pago, 2),
            'rh' => round($rh, 2),
            'folha_liquida' => round($folhaLiquida, 2),
            'lucro' => round($lucro, 2),
            'caixa' => round($caixa, 2),
            'margem' => round($margem, 2),
            'conversao_receita' => round($receitaConvertida, 2),
            'execucao_despesa' => round($despesaPagaRatio, 2),
        ];

        $mapaRisco = [
            ['nome' => 'Caixa', 'valor' => round(min(100, max(0, 100 - ($receitaConvertida))), 2)],
            ['nome' => 'Folha', 'valor' => round(min(100, max(0, (float) ($financeiro['pesoFolhaReceita'] ?? 0) * 2)), 2)],
            ['nome' => 'Margem', 'valor' => round(min(100, max(0, 100 - (($margem + 20) * 3))), 2)],
            ['nome' => 'Comprometimento', 'valor' => round(min(100, max(0, (float) ($financeiro['capitalComprometido'] ?? 0))), 2)],
        ];

        return [
            'mes' => $mes,
            'ano' => $ano,
            'scoreSaude' => $ia['scoreSaude'] ?? 0,
            'statusSaude' => $ia['statusSaude'] ?? 'atencao',
            'decisao' => $ia['decisao'] ?? ['titulo' => 'Sem decisão', 'descricao' => ''],
            'placar' => $placar,
            'insights' => $insights,
            'prioridades' => $ia['prioridades'] ?? [],
            'recomendacoes' => $ia['recomendacoes'] ?? [],
            'anomalias' => $ia['anomalias'] ?? [],
            'forecast' => $ia['forecast'] ?? ['horizonte' => [], 'resumo' => []],
            'serie' => $serie,
            'drivers' => $ia['drivers'] ?? [],
            'cenarios' => $ia['cenarios'] ?? [],
            'radar' => $ia['radar'] ?? [],
            'mapaRisco' => $mapaRisco,
            'topPagar' => $financeiro['categoriasPagar'] ?? [],
            'topReceber' => $financeiro['categoriasReceber'] ?? [],
            'dre' => $dre,
        ];
    }
}
