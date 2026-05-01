<?php

namespace App\Modules\RH\Services;

class RHDecisionEngineService
{
    public function __construct(private RHFinanceiroIntegrationService $financeiro)
    {
    }

    public function analisar(int $empresaId, int $mes, int $ano): array
    {
        $snapshot = $this->financeiro->competencia($empresaId, $mes, $ano);
        $serie = $this->financeiro->serieMensal($empresaId, $mes, $ano, 6);
        $custosRh = $snapshot['custosRh'];
        $colaboradores = max((int) ($custosRh['colaboradores'] ?? 0), 1);
        $receitaPorColaborador = $snapshot['receitaPrevista'] / $colaboradores;
        $custoPorColaborador = ($custosRh['total_rh'] ?? 0) / $colaboradores;
        $mediaSalarial = ($custosRh['salarios'] ?? 0) / $colaboradores;
        $margemAtual = $snapshot['receitaPrevista'] > 0 ? ($snapshot['resultadoPrevisto'] / $snapshot['receitaPrevista']) * 100 : 0;

        $score = 100;
        $score -= $this->penalidadeFaixa($snapshot['pesoFolhaReceita'], [[30,0],[35,8],[40,16],[50,28],[999,40]]);
        $score -= $this->penalidadeFaixa($snapshot['capitalComprometido'], [[75,0],[90,8],[100,16],[115,28],[999,40]]);
        $score -= $snapshot['resultadoPrevisto'] < 0 ? 20 : 0;
        $score -= $snapshot['resultadoCaixa'] < 0 ? 15 : 0;
        $score -= $snapshot['coberturaFolha'] < 1 ? 18 : ($snapshot['coberturaFolha'] < 1.5 ? 8 : 0);
        $score = max(0, min(100, $score));

        $status = $score >= 80 ? 'saudavel' : ($score >= 60 ? 'atencao' : 'critico');
        $decisao = $this->definirDecisao($status, $snapshot);
        $cenarios = $this->montarCenarios($snapshot, $mediaSalarial);
        $drivers = $this->montarDrivers($snapshot, $receitaPorColaborador, $custoPorColaborador, $margemAtual);
        $tendencias = $this->montarTendencias($serie);
        $anomalias = $this->detectarAnomalias($snapshot, $serie);
        $forecast = $this->montarForecast($snapshot, $serie, $mediaSalarial);
        $prioridades = $this->montarPrioridades($snapshot, $status, $tendencias, $anomalias);
        $recomendacoes = $this->montarRecomendacoes($snapshot, $status, $decisao, $drivers, $cenarios, $anomalias, $forecast);
        $radar = $this->montarRadar($snapshot, $score, $tendencias);

        return [
            'mes' => $mes,
            'ano' => $ano,
            'receita' => $snapshot['receitaPrevista'],
            'receitaRecebida' => $snapshot['receitaRecebida'],
            'despesasOperacionais' => $snapshot['despesaSemRh'],
            'rh' => $snapshot['custosRh']['total_rh'] ?? 0,
            'resultado' => $snapshot['resultadoPrevisto'],
            'resultadoCaixa' => $snapshot['resultadoCaixa'],
            'pesoFolha' => $snapshot['pesoFolhaReceita'],
            'pesoFolhaCaixa' => $snapshot['pesoFolhaCaixa'],
            'capitalComprometido' => $snapshot['capitalComprometido'],
            'margem' => $margemAtual,
            'ticketMedioFuncionario' => $custoPorColaborador,
            'funcionariosAtivos' => $custosRh['colaboradores'] ?? 0,
            'scoreSaude' => $score,
            'statusSaude' => $status,
            'decisao' => $decisao,
            'drivers' => $drivers,
            'recomendacoes' => $recomendacoes,
            'prioridades' => $prioridades,
            'cenarios' => $cenarios,
            'serie' => $serie,
            'tendencias' => $tendencias,
            'anomalias' => $anomalias,
            'forecast' => $forecast,
            'radar' => $radar,
            'categoriasPagar' => $snapshot['categoriasPagar'],
            'categoriasReceber' => $snapshot['categoriasReceber'],
        ];
    }

    private function definirDecisao(string $status, array $snapshot): array
    {
        if ($status === 'critico') {
            return [
                'codigo' => 'congelar',
                'titulo' => 'Congelar contratações e proteger caixa',
                'descricao' => 'O cenário atual não sustenta aumento de equipe sem ajuste prévio de despesas, cobrança e recuperação de margem.',
            ];
        }

        if ($status === 'atencao') {
            return [
                'codigo' => 'contratacao-seletiva',
                'titulo' => 'Contratação seletiva, somente por retorno direto',
                'descricao' => 'Só contrate em áreas que aumentem receita, reduzam fila ou substituam gargalos operacionais claros e mensuráveis.',
            ];
        }

        if (($snapshot['pesoFolhaReceita'] ?? 0) <= 30 && ($snapshot['resultadoPrevisto'] ?? 0) > 0) {
            return [
                'codigo' => 'crescer',
                'titulo' => 'Há espaço para crescimento controlado',
                'descricao' => 'A empresa suporta expansão desde que preserve margem, eficiência de equipe e disciplina financeira.',
            ];
        }

        return [
            'codigo' => 'manter',
            'titulo' => 'Manter equipe e acelerar produtividade',
            'descricao' => 'O cenário está equilibrado, mas ainda pede foco em cobrança, produtividade e alocação mais inteligente do time.',
        ];
    }

    private function montarDrivers(array $snapshot, float $receitaPorColaborador, float $custoPorColaborador, float $margemAtual): array
    {
        return [
            [
                'titulo' => 'Receita por colaborador',
                'valor' => round($receitaPorColaborador, 2),
                'meta' => round($custoPorColaborador * 2.2, 2),
                'status' => $receitaPorColaborador >= ($custoPorColaborador * 2.2) ? 'bom' : 'atencao',
                'descricao' => 'Compara geração de receita com o custo médio total por colaborador.',
            ],
            [
                'titulo' => 'Cobertura de folha pelo caixa',
                'valor' => round((float) ($snapshot['coberturaFolha'] ?? 0), 2),
                'meta' => 1.5,
                'status' => ($snapshot['coberturaFolha'] ?? 0) >= 1.5 ? 'bom' : (($snapshot['coberturaFolha'] ?? 0) >= 1 ? 'atencao' : 'critico'),
                'descricao' => 'Quantas vezes a receita recebida do mês cobre a folha líquida.',
            ],
            [
                'titulo' => 'Capital comprometido',
                'valor' => round((float) ($snapshot['capitalComprometido'] ?? 0), 2),
                'meta' => 90,
                'status' => ($snapshot['capitalComprometido'] ?? 0) <= 90 ? 'bom' : (($snapshot['capitalComprometido'] ?? 0) <= 100 ? 'atencao' : 'critico'),
                'descricao' => 'Percentual da receita prevista já comprometido com despesas e RH.',
            ],
            [
                'titulo' => 'Margem operacional',
                'valor' => round($margemAtual, 2),
                'meta' => 12,
                'status' => $margemAtual >= 12 ? 'bom' : ($margemAtual >= 5 ? 'atencao' : 'critico'),
                'descricao' => 'Margem após despesas e custo total de RH.',
            ],
        ];
    }

    private function montarCenarios(array $snapshot, float $mediaSalarial): array
    {
        $salarioBase = $mediaSalarial > 0 ? $mediaSalarial : 2000;
        return [
            $this->impactoContratacao($snapshot, 1, $salarioBase),
            $this->impactoContratacao($snapshot, 2, $salarioBase),
            $this->impactoReajuste($snapshot, 0.05),
            $this->impactoChoqueReceita($snapshot, -0.10),
            $this->impactoInadimplencia($snapshot, 0.15),
        ];
    }

    private function impactoContratacao(array $snapshot, int $quantidade, float $salario): array
    {
        $custoUnitario = $salario * 1.44;
        $impacto = $quantidade * $custoUnitario;
        $resultadoProjetado = ($snapshot['resultadoPrevisto'] ?? 0) - $impacto;
        $margemProjetada = ($snapshot['receitaPrevista'] ?? 0) > 0 ? ($resultadoProjetado / $snapshot['receitaPrevista']) * 100 : 0;

        return [
            'tipo' => 'contratacao',
            'titulo' => 'Simulação: +' . $quantidade . ' contratação' . ($quantidade > 1 ? 'es' : ''),
            'descricao' => 'Considera salário médio atual + encargos + provisões.',
            'impacto' => round($impacto, 2),
            'resultado' => round($resultadoProjetado, 2),
            'margem' => round($margemProjetada, 2),
            'status' => $resultadoProjetado >= 0 ? 'bom' : 'critico',
        ];
    }

    private function impactoReajuste(array $snapshot, float $percentual): array
    {
        $base = (float) ($snapshot['custosRh']['salarios'] ?? 0);
        $impacto = $base * $percentual * 1.36;
        $resultadoProjetado = ($snapshot['resultadoPrevisto'] ?? 0) - $impacto;
        $margemProjetada = ($snapshot['receitaPrevista'] ?? 0) > 0 ? ($resultadoProjetado / $snapshot['receitaPrevista']) * 100 : 0;

        return [
            'tipo' => 'reajuste',
            'titulo' => 'Simulação: reajuste geral de 5%',
            'descricao' => 'Aplica reajuste sobre salários base com reflexo de encargos e provisões.',
            'impacto' => round($impacto, 2),
            'resultado' => round($resultadoProjetado, 2),
            'margem' => round($margemProjetada, 2),
            'status' => $resultadoProjetado >= 0 ? 'atencao' : 'critico',
        ];
    }

    private function impactoChoqueReceita(array $snapshot, float $percentual): array
    {
        $queda = (float) ($snapshot['receitaPrevista'] ?? 0) * abs($percentual);
        $resultadoProjetado = (float) ($snapshot['resultadoPrevisto'] ?? 0) - $queda;
        $novaReceita = (float) ($snapshot['receitaPrevista'] ?? 0) - $queda;
        $margemProjetada = $novaReceita > 0 ? ($resultadoProjetado / $novaReceita) * 100 : 0;

        return [
            'tipo' => 'receita',
            'titulo' => 'Estresse: queda de 10% na receita',
            'descricao' => 'Mostra a sensibilidade do resultado caso a receita recue no próximo ciclo.',
            'impacto' => round($queda, 2),
            'resultado' => round($resultadoProjetado, 2),
            'margem' => round($margemProjetada, 2),
            'status' => $resultadoProjetado >= 0 ? 'atencao' : 'critico',
        ];
    }

    private function impactoInadimplencia(array $snapshot, float $percentual): array
    {
        $impacto = ((float) ($snapshot['receitaPrevista'] ?? 0) - (float) ($snapshot['receitaRecebida'] ?? 0)) * (1 + $percentual);
        $resultadoProjetado = (float) ($snapshot['resultadoCaixa'] ?? 0) - $impacto;
        $margemProjetada = (float) ($snapshot['receitaRecebida'] ?? 0) > 0 ? ($resultadoProjetado / (float) $snapshot['receitaRecebida']) * 100 : 0;

        return [
            'tipo' => 'inadimplencia',
            'titulo' => 'Estresse: inadimplência +15%',
            'descricao' => 'Evidencia o efeito de recebimento abaixo do previsto sobre caixa e folha.',
            'impacto' => round($impacto, 2),
            'resultado' => round($resultadoProjetado, 2),
            'margem' => round($margemProjetada, 2),
            'status' => $resultadoProjetado >= 0 ? 'atencao' : 'critico',
        ];
    }

    private function montarRecomendacoes(array $snapshot, string $status, array $decisao, array $drivers, array $cenarios, array $anomalias, array $forecast): array
    {
        $recomendacoes = [[
            'tipo' => $status === 'saudavel' ? 'positivo' : ($status === 'atencao' ? 'alerta' : 'critico'),
            'titulo' => $decisao['titulo'],
            'texto' => $decisao['descricao'],
        ]];

        if (($snapshot['pesoFolhaReceita'] ?? 0) > 40) {
            $recomendacoes[] = [
                'tipo' => 'critico',
                'titulo' => 'Folha pesada sobre a receita',
                'texto' => 'Reduza horas extras, benefícios fora de política e contratações sem payback direto antes do próximo fechamento.',
            ];
        }

        if (($snapshot['coberturaFolha'] ?? 0) < 1) {
            $recomendacoes[] = [
                'tipo' => 'critico',
                'titulo' => 'Caixa recebido não cobre a folha líquida',
                'texto' => 'Acelere cobrança, renegocie vencimentos e evite antecipar custos fixos até recuperar cobertura mínima.',
            ];
        }

        if (($snapshot['resultadoPrevisto'] ?? 0) > 0 && ($snapshot['pesoFolhaReceita'] ?? 0) <= 30) {
            $recomendacoes[] = [
                'tipo' => 'positivo',
                'titulo' => 'Espaço para ganho de produtividade',
                'texto' => 'Use a folga de margem para contratar apenas funções com impacto em receita ou SLA.',
            ];
        }

        $cenarioCritico = collect($cenarios)->firstWhere('status', 'critico');
        if ($cenarioCritico) {
            $recomendacoes[] = [
                'tipo' => 'alerta',
                'titulo' => 'Simulação mostra risco em novas decisões',
                'texto' => $cenarioCritico['titulo'] . ' derruba o resultado projetado para R$ ' . number_format((float) $cenarioCritico['resultado'], 2, ',', '.') . '.',
            ];
        }

        foreach (array_slice($anomalias, 0, 2) as $anomalia) {
            $recomendacoes[] = [
                'tipo' => $anomalia['tipo'],
                'titulo' => $anomalia['titulo'],
                'texto' => $anomalia['texto'],
            ];
        }

        if (($forecast['resumo']['risco_medio'] ?? 0) >= 60) {
            $recomendacoes[] = [
                'tipo' => 'alerta',
                'titulo' => 'Previsão dos próximos 3 meses pede proteção',
                'texto' => 'A média de risco do horizonte preditivo está elevada. Priorize caixa, cobrança e revisão das despesas fixas.',
            ];
        }

        return $recomendacoes;
    }

    private function montarPrioridades(array $snapshot, string $status, array $tendencias, array $anomalias): array
    {
        $prioridades = [];

        $prioridades[] = [
            'titulo' => 'Proteger caixa operacional',
            'prazo' => '7 dias',
            'impacto' => $status === 'critico' ? 'alto' : 'medio',
            'descricao' => 'Atacar vencidos, renegociar saídas e alinhar desembolsos do mês com cobertura de folha.',
        ];

        if (($snapshot['pesoFolhaReceita'] ?? 0) > 35) {
            $prioridades[] = [
                'titulo' => 'Revisar alocação e horas extras',
                'prazo' => '15 dias',
                'impacto' => 'alto',
                'descricao' => 'Folha passou do patamar ideal. Reavalie escala, banco de horas, ociosidade e funções com menor retorno.',
            ];
        }

        if (($tendencias['receita'] ?? 0) < 0) {
            $prioridades[] = [
                'titulo' => 'Plano comercial para recuperar receita',
                'prazo' => '30 dias',
                'impacto' => 'alto',
                'descricao' => 'Receita em tendência de queda. Direcione ações comerciais e retenção de carteira.',
            ];
        }

        if (!empty($anomalias)) {
            $prioridades[] = [
                'titulo' => 'Corrigir desvios identificados pela IA',
                'prazo' => '10 dias',
                'impacto' => 'medio',
                'descricao' => 'Existem desvios fora do padrão recente. Ataque as causas-raiz antes do próximo fechamento.',
            ];
        }

        return array_slice($prioridades, 0, 4);
    }

    private function detectarAnomalias(array $snapshot, array $serie): array
    {
        if (count($serie) < 3) {
            return [];
        }

        $base = array_slice($serie, 0, count($serie) - 1);
        $mediaReceita = $this->media(array_column($base, 'receita_prevista'));
        $mediaRh = $this->media(array_column($base, 'rh_total'));
        $mediaPeso = $this->media(array_column($base, 'peso_folha'));
        $anomalias = [];

        if ($mediaReceita > 0 && ($snapshot['receitaPrevista'] ?? 0) < $mediaReceita * 0.85) {
            $anomalias[] = [
                'tipo' => 'critico',
                'titulo' => 'Receita abaixo do padrão recente',
                'texto' => 'A receita prevista do mês está mais de 15% abaixo da média recente, sinalizando risco comercial.',
            ];
        }

        if ($mediaRh > 0 && ($snapshot['custosRh']['total_rh'] ?? 0) > $mediaRh * 1.15) {
            $anomalias[] = [
                'tipo' => 'alerta',
                'titulo' => 'RH acima do padrão recente',
                'texto' => 'O custo total de RH acelerou mais de 15% contra a média dos meses anteriores.',
            ];
        }

        if ($mediaPeso > 0 && ($snapshot['pesoFolhaReceita'] ?? 0) > $mediaPeso * 1.12) {
            $anomalias[] = [
                'tipo' => 'alerta',
                'titulo' => 'Peso da folha saiu da curva',
                'texto' => 'A relação folha/receita subiu acima do padrão recente e exige ação de produtividade ou margem.',
            ];
        }

        return $anomalias;
    }

    private function montarForecast(array $snapshot, array $serie, float $mediaSalarial): array
    {
        $baseReceita = (float) ($snapshot['receitaPrevista'] ?? 0);
        $baseRh = (float) ($snapshot['custosRh']['total_rh'] ?? 0);
        $baseDespesa = (float) ($snapshot['despesaPrevista'] ?? 0);
        $taxaReceita = $this->normalizarTaxa(($this->montarTendencias($serie)['receita'] ?? 0) / 100, -0.2, 0.2);
        $taxaRh = $this->normalizarTaxa(($this->montarTendencias($serie)['rh'] ?? 0) / 100, -0.1, 0.2);
        $taxaDespesa = $this->normalizarTaxa(($this->montarTendencias($serie)['resultado'] ?? 0) < 0 ? 0.03 : 0.01, -0.05, 0.08);
        $horizonte = [];

        for ($i = 1; $i <= 3; $i++) {
            $receita = $baseReceita * pow(1 + $taxaReceita, $i);
            $rh = $baseRh * pow(1 + $taxaRh, $i);
            $despesa = $baseDespesa * pow(1 + $taxaDespesa, $i);
            $resultado = $receita - $despesa;
            $margem = $receita > 0 ? ($resultado / $receita) * 100 : 0;
            $risco = $this->riscoMesProjetado($receita, $rh, $resultado, $mediaSalarial);

            $horizonte[] = [
                'ordem' => $i,
                'label' => 'M+' . $i,
                'receita' => round($receita, 2),
                'rh' => round($rh, 2),
                'resultado' => round($resultado, 2),
                'margem' => round($margem, 2),
                'risco' => $risco,
                'status' => $risco >= 70 ? 'critico' : ($risco >= 45 ? 'atencao' : 'bom'),
            ];
        }

        return [
            'horizonte' => $horizonte,
            'resumo' => [
                'risco_medio' => round($this->media(array_column($horizonte, 'risco')), 2),
                'pior_resultado' => round(min(array_column($horizonte, 'resultado')), 2),
                'melhor_margem' => round(max(array_column($horizonte, 'margem')), 2),
            ],
        ];
    }

    private function montarRadar(array $snapshot, float $score, array $tendencias): array
    {
        return [
            ['label' => 'Saúde geral', 'valor' => round($score, 2)],
            ['label' => 'Eficiência da folha', 'valor' => round(max(0, 100 - (($snapshot['pesoFolhaReceita'] ?? 0) * 2)), 2)],
            ['label' => 'Cobertura de caixa', 'valor' => round(min(100, ($snapshot['coberturaFolha'] ?? 0) * 45), 2)],
            ['label' => 'Disciplina financeira', 'valor' => round(max(0, 100 - (($snapshot['capitalComprometido'] ?? 0) - 70)), 2)],
            ['label' => 'Tração de receita', 'valor' => round(min(100, max(0, 50 + (($tendencias['receita'] ?? 0) * 2))), 2)],
        ];
    }

    private function montarTendencias(array $serie): array
    {
        if (count($serie) < 2) {
            return ['receita' => 0, 'rh' => 0, 'resultado' => 0];
        }

        return [
            'receita' => round($this->variacaoEntrePontos((float) $serie[0]['receita_prevista'], (float) $serie[count($serie) - 1]['receita_prevista']), 2),
            'rh' => round($this->variacaoEntrePontos((float) $serie[0]['rh_total'], (float) $serie[count($serie) - 1]['rh_total']), 2),
            'resultado' => round($this->variacaoEntrePontos((float) $serie[0]['resultado_previsto'], (float) $serie[count($serie) - 1]['resultado_previsto']), 2),
        ];
    }

    private function penalidadeFaixa(float $valor, array $faixas): int
    {
        foreach ($faixas as [$limite, $penalidade]) {
            if ($valor <= $limite) {
                return $penalidade;
            }
        }

        return 0;
    }

    private function variacaoEntrePontos(float $primeiro, float $ultimo): float
    {
        if ($primeiro == 0.0) {
            return $ultimo > 0 ? 100.0 : 0.0;
        }

        return (($ultimo - $primeiro) / abs($primeiro)) * 100;
    }

    private function media(array $valores): float
    {
        $valores = array_values(array_filter($valores, fn ($v) => $v !== null));
        if (count($valores) === 0) {
            return 0.0;
        }

        return array_sum($valores) / count($valores);
    }

    private function normalizarTaxa(float $valor, float $min, float $max): float
    {
        return max($min, min($max, $valor));
    }

    private function riscoMesProjetado(float $receita, float $rh, float $resultado, float $mediaSalarial): float
    {
        $risco = 0.0;
        $pesoFolha = $receita > 0 ? ($rh / $receita) * 100 : 100;
        $margem = $receita > 0 ? ($resultado / $receita) * 100 : 0;

        $risco += $pesoFolha > 40 ? 35 : ($pesoFolha > 32 ? 18 : 6);
        $risco += $resultado < 0 ? 30 : ($margem < 8 ? 15 : 5);
        $risco += $mediaSalarial > 0 && $rh > ($mediaSalarial * 1.5 * 10) ? 12 : 4;

        return round(min(100, $risco), 2);
    }
}
