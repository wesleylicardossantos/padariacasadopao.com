<?php

namespace App\Services;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    public function getCardsSnapshot($empresaId, $localId = 'todos'): array
    {
        $localId = $this->normalizeLocalId($localId);
        $cacheKey = $this->buildVersionedCacheKey('dashboard:cards:%s:%s', $empresaId, $localId ?? 'todos');

        return Cache::remember($cacheKey, 60, function () use ($empresaId, $localId) {
            $inicioMes = now()->startOfMonth()->startOfDay();
            $fimMes = now()->endOfMonth()->endOfDay();
            $inicioHoje = now()->startOfDay();
            $fimHoje = now()->endOfDay();

            $vendaDateColumn = $this->resolveDateColumn('vendas');
            $vendaCaixaDateColumn = $this->resolveDateColumn('venda_caixas');

            $vendasHistorico = $this->sumSales(Venda::class, $empresaId, $localId, null, null, $vendaDateColumn)
                + $this->sumSales(VendaCaixa::class, $empresaId, $localId, null, null, $vendaCaixaDateColumn);

            $vendasMes = $this->sumSales(Venda::class, $empresaId, $localId, $inicioMes, $fimMes, $vendaDateColumn)
                + $this->sumSales(VendaCaixa::class, $empresaId, $localId, $inicioMes, $fimMes, $vendaCaixaDateColumn);

            $vendasHoje = $this->sumSales(Venda::class, $empresaId, $localId, $inicioHoje, $fimHoje, $vendaDateColumn)
                + $this->sumSales(VendaCaixa::class, $empresaId, $localId, $inicioHoje, $fimHoje, $vendaCaixaDateColumn);

            $qtdVendasMes = $this->countSales(Venda::class, $empresaId, $localId, $inicioMes, $fimMes, $vendaDateColumn)
                + $this->countSales(VendaCaixa::class, $empresaId, $localId, $inicioMes, $fimMes, $vendaCaixaDateColumn);

            $ticketMedio = $qtdVendasMes > 0 ? $vendasMes / $qtdVendasMes : 0.0;

            $contaReceber = $this->sumOpenReceivables($empresaId, $localId);

            $contaPagar = $this->sumOpenPayables($empresaId, $localId);

            $produtos = $this->countProducts($empresaId, $localId);
            $saldoPrevisto = (float) $contaReceber - (float) $contaPagar;

            return [
                'vendas' => (float) $vendasHistorico,
                'vendas_historico' => (float) $vendasHistorico,
                'vendas_mes' => (float) $vendasMes,
                'vendas_hoje' => (float) $vendasHoje,
                'produtos' => (int) $produtos,
                'conta_pagar' => (float) $contaPagar,
                'conta_receber' => (float) $contaReceber,
                'ticket_medio' => (float) $ticketMedio,
                'qtd_vendas' => (int) $qtdVendasMes,
                'saldo_previsto' => (float) $saldoPrevisto,
                'audit' => $this->buildFinancialAudit($empresaId, $localId, [
                    'vendas_mes' => (float) $vendasMes,
                    'vendas_hoje' => (float) $vendasHoje,
                    'conta_receber' => (float) $contaReceber,
                    'conta_pagar' => (float) $contaPagar,
                ]),
                'debug_data_venda' => $vendaDateColumn,
                'debug_data_venda_caixa' => $vendaCaixaDateColumn,
            ];
        });
    }

    public function getAnnualSalesSeries($empresaId, $localId = 'todos', ?int $year = null): array
    {
        $localId = $this->normalizeLocalId($localId);
        $year = $year ?: (int) date('Y');
        $currentMonth = $year === (int) date('Y') ? (int) date('n') : 12;
        $cacheKey = $this->buildVersionedCacheKey('dashboard:annual:%s:%s:%s', $empresaId, $localId ?? 'todos', $year);

        return Cache::remember($cacheKey, 300, function () use ($empresaId, $localId, $year, $currentMonth) {
            $labels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $vendaDateColumn = $this->resolveDateColumn('vendas');
            $vendaCaixaDateColumn = $this->resolveDateColumn('venda_caixas');
            $meses = [];
            $somaVendas = [];

            for ($month = 1; $month <= $currentMonth; $month++) {
                $inicio = Carbon::create($year, $month, 1)->startOfMonth()->startOfDay();
                $fim = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

                $total = $this->sumSales(Venda::class, $empresaId, $localId, $inicio, $fim, $vendaDateColumn)
                    + $this->sumSales(VendaCaixa::class, $empresaId, $localId, $inicio, $fim, $vendaCaixaDateColumn);

                $meses[] = $labels[$month - 1];
                $somaVendas[] = (float) $total;
            }

            return [
                'ano' => $year,
                'meses' => $meses,
                'somaVendas' => $somaVendas,
            ];
        });
    }

    public function getContasReceberSummary($empresaId, $localId = 'todos'): array
    {
        $localId = $this->normalizeLocalId($localId);
        $query = $this->buildContaReceberQuery($empresaId, $localId);
        $recebidas = (float) (clone $query)->where('status', true)->sum(DB::raw('COALESCE(valor_recebido, valor_integral)'));
        $receber = $this->sumOpenReceivables($empresaId, $localId);
        $sumTotal = (float) ($recebidas + $receber);

        return [
            'recebidas' => __moeda($recebidas),
            'receber' => __moeda($receber),
            'percentual' => $sumTotal > 0 ? number_format(($recebidas / $sumTotal) * 100, 0) : 0,
            'recebidas_valor' => $recebidas,
            'receber_valor' => $receber,
            'total_valor' => $sumTotal,
        ];
    }

    public function getContasPagarSummary($empresaId, $localId = 'todos'): array
    {
        $localId = $this->normalizeLocalId($localId);
        $query = $this->buildContaPagarQuery($empresaId, $localId);
        $pagos = (float) (clone $query)->where('status', true)->sum(DB::raw('COALESCE(valor_pago, valor_integral)'));
        $pagar = $this->sumOpenPayables($empresaId, $localId);
        $sumTotal = (float) ($pagos + $pagar);

        return [
            'pagos' => __moeda($pagos),
            'pagar' => __moeda($pagar),
            'percentual' => $sumTotal > 0 ? number_format(($pagos / $sumTotal) * 100, 0) : 0,
            'pagos_valor' => $pagos,
            'pagar_valor' => $pagar,
            'total_valor' => $sumTotal,
        ];
    }

    public function getFinancialAudit($empresaId, $localId = 'todos'): array
    {
        $snapshot = $this->getCardsSnapshot($empresaId, $localId);
        return $snapshot['audit'] ?? $this->buildFinancialAudit($empresaId, $this->normalizeLocalId($localId));
    }

    public function getDreSummary($empresaId, $localId = 'todos', ?int $year = null, ?int $month = null): array
    {
        $localId = $this->normalizeLocalId($localId);
        $year = $year ?: (int) date('Y');
        $month = $month ?: (int) date('n');
        $cacheKey = $this->buildVersionedCacheKey('dashboard:dre:%s:%s:%s:%s', $empresaId, $localId ?? 'todos', $year, $month);

        return Cache::remember($cacheKey, 300, function () use ($empresaId, $localId, $year, $month) {
            $period = $this->resolvePeriod($year, $month);
            $salesTotals = $this->getSalesTotalsForPeriod($empresaId, $localId, $period['start'], $period['end']);
            $deductions = $this->getSalesDiscountsForPeriod($empresaId, $localId, $period['start'], $period['end']);
            $variableCosts = $this->getVariableCostForPeriod($empresaId, $localId, $period['start'], $period['end']);
            $fixedExpenses = $this->getExpenseForPeriod($empresaId, $localId, $period['start'], $period['end']);
            $received = $this->getReceivedForPeriod($empresaId, $localId, $period['start'], $period['end']);
            $paid = $this->getPaidForPeriod($empresaId, $localId, $period['start'], $period['end']);

            $grossRevenue = (float) $salesTotals['total'];
            $netRevenue = (float) max($grossRevenue - $deductions, 0);
            $grossProfit = (float) ($netRevenue - $variableCosts);
            $netProfit = (float) ($grossProfit - $fixedExpenses);
            $margin = $netRevenue > 0 ? round(($netProfit / $netRevenue) * 100, 2) : 0.0;
            $markup = $variableCosts > 0 ? round(($grossProfit / $variableCosts) * 100, 2) : 0.0;
            $breakEven = $netRevenue > 0 ? round(($fixedExpenses / max($netRevenue, 1)) * 100, 2) : 0.0;

            $status = 'lucro';
            $statusLabel = 'Lucro real positivo';
            if ($netProfit < 0) {
                $status = 'prejuizo';
                $statusLabel = 'Prejuízo no período';
            } elseif ($margin < 8) {
                $status = 'atencao';
                $statusLabel = 'Margem em atenção';
            }

            return [
                'periodo' => $period['label'],
                'ano' => $year,
                'mes' => $month,
                'receita_bruta' => (float) $grossRevenue,
                'deducoes' => (float) $deductions,
                'receita_liquida' => (float) $netRevenue,
                'custos_variaveis' => (float) $variableCosts,
                'despesas_fixas' => (float) $fixedExpenses,
                'lucro_bruto' => (float) $grossProfit,
                'lucro_liquido' => (float) $netProfit,
                'margem_liquida_percentual' => (float) $margin,
                'markup_percentual' => (float) $markup,
                'ponto_equilibrio_percentual_receita' => (float) $breakEven,
                'recebido_periodo' => (float) $received,
                'pago_periodo' => (float) $paid,
                'status_resultado' => $status,
                'status_resultado_label' => $statusLabel,
                'fonte_custo' => 'Itens de venda/PDV com valor_custo e fallback para produto.valor_compra',
                'detalhes' => [
                    ['nome' => 'Receita bruta', 'valor' => (float) $grossRevenue],
                    ['nome' => 'Deduções comerciais', 'valor' => (float) $deductions],
                    ['nome' => 'Receita líquida', 'valor' => (float) $netRevenue],
                    ['nome' => 'Custos variáveis', 'valor' => (float) $variableCosts],
                    ['nome' => 'Despesas fixas/operacionais', 'valor' => (float) $fixedExpenses],
                    ['nome' => 'Lucro bruto', 'valor' => (float) $grossProfit],
                    ['nome' => 'Lucro líquido', 'valor' => (float) $netProfit],
                ],
                'atualizado_em' => now()->format('d/m/Y H:i:s'),
            ];
        });
    }

    public function getBiOverview($empresaId, $localId = 'todos', ?int $year = null, ?int $month = null): array
    {
        $localId = $this->normalizeLocalId($localId);
        $year = $year ?: (int) date('Y');
        $month = $month ?: (int) date('n');
        $cacheKey = $this->buildVersionedCacheKey('dashboard:bi:%s:%s:%s:%s', $empresaId, $localId ?? 'todos', $year, $month);

        return Cache::remember($cacheKey, 300, function () use ($empresaId, $localId, $year, $month) {
            $period = $this->resolvePeriod($year, $month);
            $dailySeries = $this->getDailySalesSeries($empresaId, $localId, $period['start'], $period['end']);
            $dreSeries = $this->getMonthlyDreSeries($empresaId, $localId, $year);
            $topProducts = $this->getTopProductsRanking($empresaId, $localId, $period['start'], $period['end']);
            $topClients = $this->getTopClientsRanking($empresaId, $localId, $period['start'], $period['end']);
            $paymentMethods = $this->getPaymentMethodsBreakdown($empresaId, $localId, $period['start'], $period['end']);
            $summary = $this->getDreSummary($empresaId, $localId, $year, $month);

            return [
                'periodo' => $period['label'],
                'serie_diaria' => $dailySeries,
                'dre_anual' => $dreSeries,
                'top_produtos' => $topProducts,
                'top_clientes' => $topClients,
                'formas_pagamento' => $paymentMethods,
                'resumo' => [
                    'receita_bruta' => $summary['receita_bruta'],
                    'receita_liquida' => $summary['receita_liquida'],
                    'lucro_liquido' => $summary['lucro_liquido'],
                    'margem_liquida_percentual' => $summary['margem_liquida_percentual'],
                ],
                'atualizado_em' => now()->format('d/m/Y H:i:s'),
            ];
        });
    }

    public function getPdvDivergenceAudit($empresaId, $localId = 'todos', ?int $year = null, ?int $month = null): array
    {
        $localId = $this->normalizeLocalId($localId);
        $year = $year ?: (int) date('Y');
        $month = $month ?: (int) date('n');
        $cacheKey = $this->buildVersionedCacheKey('dashboard:pdv-audit:%s:%s:%s:%s', $empresaId, $localId ?? 'todos', $year, $month);

        return Cache::remember($cacheKey, 180, function () use ($empresaId, $localId, $year, $month) {
            $period = $this->resolvePeriod($year, $month);
            $dateColumn = $this->resolveDateColumn('venda_caixas');
            $sales = VendaCaixa::query()
                ->with(['cliente'])
                ->where('empresa_id', $empresaId);
            $this->applyFilialFilter($sales, $localId, true);
            $this->applySalesValidityFilters($sales, new VendaCaixa());
            $sales->whereBetween($dateColumn, [$period['start'], $period['end']]);

            $salesList = $sales->get(['id', 'cliente_id', 'nome', 'valor_total', 'tipo_pagamento', 'forma_pagamento', 'filial_id', $dateColumn]);
            $ids = $salesList->pluck('id')->all();

            if (empty($ids)) {
                return [
                    'periodo' => $period['label'],
                    'total_vendas_pdv' => 0.0,
                    'total_financeiro_pdv' => 0.0,
                    'diferenca_total' => 0.0,
                    'quantidade_vendas_pdv' => 0,
                    'vendas_sem_conta' => 0,
                    'vendas_com_divergencia' => 0,
                    'status' => 'saudavel',
                    'status_label' => 'Sem divergências no período',
                    'itens' => [],
                    'atualizado_em' => now()->format('d/m/Y H:i:s'),
                ];
            }

            $financeiro = ContaReceber::query()
                ->select('venda_caixa_id', DB::raw('SUM(valor_integral) as valor_integral_total'), DB::raw('SUM(valor_recebido) as valor_recebido_total'))
                ->where('empresa_id', $empresaId)
                ->whereIn('venda_caixa_id', $ids)
                ->groupBy('venda_caixa_id')
                ->get()
                ->keyBy('venda_caixa_id');

            $items = [];
            $totalSales = 0.0;
            $totalFinancial = 0.0;
            $withoutAccount = 0;
            $withDivergence = 0;

            foreach ($salesList as $sale) {
                $financial = $financeiro->get($sale->id);
                $financialValue = (float) ($financial->valor_integral_total ?? 0);
                $difference = (float) $sale->valor_total - $financialValue;
                $hasAccount = $financial !== null;

                $totalSales += (float) $sale->valor_total;
                $totalFinancial += $financialValue;

                if (!$hasAccount) {
                    $withoutAccount++;
                }

                if (abs($difference) > 0.01) {
                    $withDivergence++;
                }

                if (!$hasAccount || abs($difference) > 0.01) {
                    $items[] = [
                        'venda_caixa_id' => $sale->id,
                        'cliente' => $this->resolveClientLabel($sale),
                        'data' => optional($sale->{$dateColumn} instanceof Carbon ? $sale->{$dateColumn} : Carbon::parse($sale->{$dateColumn}))->format('d/m/Y H:i'),
                        'valor_venda' => (float) $sale->valor_total,
                        'valor_financeiro' => (float) $financialValue,
                        'valor_recebido' => (float) ($financial->valor_recebido_total ?? 0),
                        'diferenca' => (float) $difference,
                        'forma_pagamento' => $this->resolvePaymentLabel($sale->tipo_pagamento, $sale->forma_pagamento),
                        'status' => !$hasAccount ? 'sem_conta' : 'divergente',
                    ];
                }
            }

            usort($items, function ($a, $b) {
                return abs($b['diferenca']) <=> abs($a['diferenca']);
            });

            $differenceTotal = (float) ($totalSales - $totalFinancial);
            $status = 'saudavel';
            $statusLabel = 'Sem divergências relevantes';
            if ($withoutAccount > 0 || abs($differenceTotal) > 0.01) {
                $status = 'atencao';
                $statusLabel = 'Divergências encontradas no PDV';
            }
            if ($withoutAccount > 3 || abs($differenceTotal) > max(50, $totalSales * 0.03)) {
                $status = 'critico';
                $statusLabel = 'Conciliação PDV requer ação imediata';
            }

            return [
                'periodo' => $period['label'],
                'total_vendas_pdv' => (float) $totalSales,
                'total_financeiro_pdv' => (float) $totalFinancial,
                'diferenca_total' => (float) $differenceTotal,
                'quantidade_vendas_pdv' => (int) count($ids),
                'vendas_sem_conta' => (int) $withoutAccount,
                'vendas_com_divergencia' => (int) $withDivergence,
                'status' => $status,
                'status_label' => $statusLabel,
                'itens' => array_slice($items, 0, 20),
                'atualizado_em' => now()->format('d/m/Y H:i:s'),
            ];
        });
    }

    public function normalizeLocalId($localId)
    {
        if ($localId === null || $localId === '' || $localId === 'null' || $localId === 'undefined' || $localId === false) {
            return 'todos';
        }

        return (string) $localId;
    }

    public function bumpCacheVersion($empresaId): int
    {
        $key = $this->getDashboardVersionKey($empresaId);
        $next = ((int) Cache::get($key, 1)) + 1;
        Cache::forever($key, $next);

        return $next;
    }

    private function buildVersionedCacheKey(string $pattern, ...$segments): string
    {
        $empresaId = $segments[0] ?? 'global';
        $baseKey = sprintf($pattern, ...$segments);

        return $baseKey . ':v' . $this->getCacheVersion($empresaId);
    }

    private function getCacheVersion($empresaId): int
    {
        return (int) Cache::get($this->getDashboardVersionKey($empresaId), 1);
    }

    private function getDashboardVersionKey($empresaId): string
    {
        return sprintf('dashboard:version:%s', $empresaId ?? 'global');
    }

    private function buildFinancialAudit($empresaId, $localId, array $base = []): array
    {
        $vendaDateColumn = $this->resolveDateColumn('vendas');
        $vendaCaixaDateColumn = $this->resolveDateColumn('venda_caixas');
        $inicioMes = now()->startOfMonth()->startOfDay();
        $fimMes = now()->endOfMonth()->endOfDay();

        $vendaMes = $this->sumSales(Venda::class, $empresaId, $localId, $inicioMes, $fimMes, $vendaDateColumn);
        $vendaCaixaMes = $this->sumSales(VendaCaixa::class, $empresaId, $localId, $inicioMes, $fimMes, $vendaCaixaDateColumn);
        $recebidoNoMes = (float) $this->buildContaReceberQuery($empresaId, $localId)
            ->where('status', true)
            ->whereBetween('data_recebimento', [$inicioMes->toDateString(), $fimMes->toDateString()])
            ->sum('valor_recebido');
        $pagoNoMes = (float) $this->buildContaPagarQuery($empresaId, $localId)
            ->where('status', true)
            ->whereBetween('data_pagamento', [$inicioMes->toDateString(), $fimMes->toDateString()])
            ->sum('valor_pago');

        $faturamentoMes = (float) ($base['vendas_mes'] ?? ($vendaMes + $vendaCaixaMes));
        $contaReceber = (float) ($base['conta_receber'] ?? $this->sumOpenReceivables($empresaId, $localId));
        $contaPagar = (float) ($base['conta_pagar'] ?? $this->sumOpenPayables($empresaId, $localId));
        $saldoProjetado = $contaReceber - $contaPagar;
        $diferencaFaturadoRecebido = (float) ($faturamentoMes - $recebidoNoMes);
        $percentualRecebido = $faturamentoMes > 0 ? round(($recebidoNoMes / $faturamentoMes) * 100, 2) : 0.0;
        $percentualPago = $contaPagar > 0 ? round(($pagoNoMes / ($pagoNoMes + $contaPagar)) * 100, 2) : 0.0;

        $status = 'saudavel';
        $statusLabel = 'Saudável';
        if ($saldoProjetado < 0 || $diferencaFaturadoRecebido > ($faturamentoMes * 0.25)) {
            $status = 'critico';
            $statusLabel = 'Crítico';
        } elseif ($diferencaFaturadoRecebido > ($faturamentoMes * 0.10) || $contaPagar > $contaReceber) {
            $status = 'atencao';
            $statusLabel = 'Atenção';
        }

        return [
            'faturamento_venda' => (float) $vendaMes,
            'faturamento_venda_caixa' => (float) $vendaCaixaMes,
            'faturamento_total_mes' => (float) $faturamentoMes,
            'recebido_no_mes' => (float) $recebidoNoMes,
            'pago_no_mes' => (float) $pagoNoMes,
            'contas_receber_aberto' => (float) $contaReceber,
            'contas_pagar_aberto' => (float) $contaPagar,
            'saldo_projetado' => (float) $saldoProjetado,
            'diferenca_faturado_recebido' => $diferencaFaturadoRecebido,
            'percentual_recebido_faturado' => $percentualRecebido,
            'percentual_pago_obrigacoes' => $percentualPago,
            'status_fluxo' => $status,
            'status_fluxo_label' => $statusLabel,
            'atualizado_em' => now()->format('d/m/Y H:i:s'),
        ];
    }

    private function getDailySalesSeries($empresaId, $localId, Carbon $start, Carbon $end): array
    {
        $vendaDateColumn = $this->resolveDateColumn('vendas');
        $vendaCaixaDateColumn = $this->resolveDateColumn('venda_caixas');
        $days = [];
        $values = [];

        $current = $start->copy();
        while ($current <= $end) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();
            $value = $this->sumSales(Venda::class, $empresaId, $localId, $dayStart, $dayEnd, $vendaDateColumn)
                + $this->sumSales(VendaCaixa::class, $empresaId, $localId, $dayStart, $dayEnd, $vendaCaixaDateColumn);
            $days[] = $current->format('d/m');
            $values[] = (float) $value;
            $current->addDay();
        }

        return ['dias' => $days, 'valores' => $values];
    }

    private function getMonthlyDreSeries($empresaId, $localId, int $year): array
    {
        $labels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $receita = [];
        $custos = [];
        $lucro = [];
        $limit = $year === (int) date('Y') ? (int) date('n') : 12;

        for ($month = 1; $month <= $limit; $month++) {
            $dre = $this->getDreSummary($empresaId, $localId, $year, $month);
            $receita[] = (float) $dre['receita_liquida'];
            $custos[] = (float) ($dre['custos_variaveis'] + $dre['despesas_fixas']);
            $lucro[] = (float) $dre['lucro_liquido'];
        }

        return [
            'ano' => $year,
            'meses' => array_slice($labels, 0, $limit),
            'receita_liquida' => $receita,
            'custos_total' => $custos,
            'lucro_liquido' => $lucro,
        ];
    }

    private function getTopProductsRanking($empresaId, $localId, Carbon $start, Carbon $end): array
    {
        $items = collect();

        $items = $items->merge($this->queryTopProductRows(
            'item_vendas',
            'vendas',
            'venda_id',
            $this->resolveDateColumn('vendas'),
            $empresaId,
            $localId,
            $start,
            $end
        ));

        $items = $items->merge($this->queryTopProductRows(
            'item_venda_caixas',
            'venda_caixas',
            'venda_caixa_id',
            $this->resolveDateColumn('venda_caixas'),
            $empresaId,
            $localId,
            $start,
            $end
        ));

        return $items
            ->groupBy('produto_id')
            ->map(function (Collection $group) {
                return [
                    'produto_id' => $group->first()['produto_id'],
                    'produto' => $group->first()['produto'],
                    'quantidade' => (float) $group->sum('quantidade'),
                    'faturamento' => (float) $group->sum('faturamento'),
                    'custo' => (float) $group->sum('custo'),
                    'lucro' => (float) $group->sum('lucro'),
                ];
            })
            ->sortByDesc('faturamento')
            ->values()
            ->take(10)
            ->all();
    }

    private function queryTopProductRows(string $itemsTable, string $salesTable, string $foreignKey, string $dateColumn, $empresaId, $localId, Carbon $start, Carbon $end): Collection
    {
        $query = DB::table($itemsTable)
            ->join($salesTable, $salesTable . '.id', '=', $itemsTable . '.' . $foreignKey)
            ->leftJoin('produtos', 'produtos.id', '=', $itemsTable . '.produto_id')
            ->where($salesTable . '.empresa_id', $empresaId)
            ->whereBetween($salesTable . '.' . $dateColumn, [$start, $end])
            ->selectRaw(
                $itemsTable . '.produto_id as produto_id, COALESCE(produtos.nome, "Sem nome") as produto, '
                . 'SUM(COALESCE(' . $itemsTable . '.quantidade, 0)) as quantidade, '
                . 'SUM(COALESCE(' . $itemsTable . '.quantidade, 0) * COALESCE(' . $itemsTable . '.valor, 0)) as faturamento, '
                . 'SUM(COALESCE(' . $itemsTable . '.quantidade, 0) * COALESCE(NULLIF(' . $itemsTable . '.valor_custo, 0), produtos.valor_compra, 0)) as custo'
            )
            ->groupBy($itemsTable . '.produto_id', 'produtos.nome');

        if ($localId !== 'todos' && Schema::hasColumn($salesTable, 'filial_id')) {
            if ($localId === '-1') {
                $query->whereNull($salesTable . '.filial_id');
            } else {
                $query->where(function ($q) use ($salesTable, $localId) {
                    $q->where($salesTable . '.filial_id', $localId)
                        ->orWhereNull($salesTable . '.filial_id');
                });
            }
        }

        return $query->get()->map(function ($row) {
            $row = (array) $row;
            $row['quantidade'] = (float) ($row['quantidade'] ?? 0);
            $row['faturamento'] = (float) ($row['faturamento'] ?? 0);
            $row['custo'] = (float) ($row['custo'] ?? 0);
            $row['lucro'] = (float) ($row['faturamento'] - $row['custo']);
            return $row;
        });
    }

    private function getTopClientsRanking($empresaId, $localId, Carbon $start, Carbon $end): array
    {
        $ranking = [];

        $vendas = Venda::query()->with('cliente')->where('empresa_id', $empresaId);
        $this->applyFilialFilter($vendas, $localId, true);
        $this->applySalesValidityFilters($vendas, new Venda());
        $vendas->whereBetween($this->resolveDateColumn('vendas'), [$start, $end]);
        foreach ($vendas->get(['cliente_id', 'valor_total']) as $sale) {
            $label = $this->resolveClientLabel($sale);
            if (!isset($ranking[$label])) {
                $ranking[$label] = ['cliente' => $label, 'quantidade_vendas' => 0, 'faturamento' => 0.0];
            }
            $ranking[$label]['quantidade_vendas']++;
            $ranking[$label]['faturamento'] += (float) $sale->valor_total;
        }

        $caixas = VendaCaixa::query()->with('cliente')->where('empresa_id', $empresaId);
        $this->applyFilialFilter($caixas, $localId, true);
        $this->applySalesValidityFilters($caixas, new VendaCaixa());
        $caixas->whereBetween($this->resolveDateColumn('venda_caixas'), [$start, $end]);
        foreach ($caixas->get(['cliente_id', 'nome', 'valor_total']) as $sale) {
            $label = $this->resolveClientLabel($sale);
            if (!isset($ranking[$label])) {
                $ranking[$label] = ['cliente' => $label, 'quantidade_vendas' => 0, 'faturamento' => 0.0];
            }
            $ranking[$label]['quantidade_vendas']++;
            $ranking[$label]['faturamento'] += (float) $sale->valor_total;
        }

        usort($ranking, function ($a, $b) {
            return $b['faturamento'] <=> $a['faturamento'];
        });

        return array_slice(array_values($ranking), 0, 10);
    }

    private function getPaymentMethodsBreakdown($empresaId, $localId, Carbon $start, Carbon $end): array
    {
        $breakdown = [];

        $vendas = Venda::query()->where('empresa_id', $empresaId);
        $this->applyFilialFilter($vendas, $localId, true);
        $this->applySalesValidityFilters($vendas, new Venda());
        $vendas->whereBetween($this->resolveDateColumn('vendas'), [$start, $end]);
        foreach ($vendas->get(['tipo_pagamento', 'forma_pagamento', 'valor_total']) as $sale) {
            $label = $this->resolvePaymentLabel($sale->tipo_pagamento, $sale->forma_pagamento);
            $breakdown[$label] = ($breakdown[$label] ?? 0) + (float) $sale->valor_total;
        }

        $caixas = VendaCaixa::query()->where('empresa_id', $empresaId);
        $this->applyFilialFilter($caixas, $localId, true);
        $this->applySalesValidityFilters($caixas, new VendaCaixa());
        $caixas->whereBetween($this->resolveDateColumn('venda_caixas'), [$start, $end]);
        foreach ($caixas->get(['tipo_pagamento', 'forma_pagamento', 'valor_total']) as $sale) {
            $label = $this->resolvePaymentLabel($sale->tipo_pagamento, $sale->forma_pagamento);
            $breakdown[$label] = ($breakdown[$label] ?? 0) + (float) $sale->valor_total;
        }

        arsort($breakdown);

        return [
            'labels' => array_keys($breakdown),
            'valores' => array_values($breakdown),
        ];
    }

    private function getSalesTotalsForPeriod($empresaId, $localId, Carbon $start, Carbon $end): array
    {
        $venda = $this->sumSales(Venda::class, $empresaId, $localId, $start, $end, $this->resolveDateColumn('vendas'));
        $caixa = $this->sumSales(VendaCaixa::class, $empresaId, $localId, $start, $end, $this->resolveDateColumn('venda_caixas'));

        return [
            'venda' => (float) $venda,
            'caixa' => (float) $caixa,
            'total' => (float) ($venda + $caixa),
        ];
    }

    private function getSalesDiscountsForPeriod($empresaId, $localId, Carbon $start, Carbon $end): float
    {
        $total = 0.0;

        foreach ([['model' => Venda::class, 'table' => 'vendas'], ['model' => VendaCaixa::class, 'table' => 'venda_caixas']] as $source) {
            $query = $source['model']::query()->where('empresa_id', $empresaId);
            $this->applyFilialFilter($query, $localId, true);
            $this->applySalesValidityFilters($query, new $source['model']);
            $query->whereBetween($this->resolveDateColumn($source['table']), [$start, $end]);
            $total += (float) $query->sum('desconto');
        }

        return (float) $total;
    }

    private function getVariableCostForPeriod($empresaId, $localId, Carbon $start, Carbon $end): float
    {
        $vendaCost = $this->sumItemCost('item_vendas', 'vendas', 'venda_id', $this->resolveDateColumn('vendas'), $empresaId, $localId, $start, $end);
        $caixaCost = $this->sumItemCost('item_venda_caixas', 'venda_caixas', 'venda_caixa_id', $this->resolveDateColumn('venda_caixas'), $empresaId, $localId, $start, $end);
        return (float) ($vendaCost + $caixaCost);
    }

    private function sumItemCost(string $itemsTable, string $salesTable, string $foreignKey, string $dateColumn, $empresaId, $localId, Carbon $start, Carbon $end): float
    {
        $query = DB::table($itemsTable)
            ->join($salesTable, $salesTable . '.id', '=', $itemsTable . '.' . $foreignKey)
            ->leftJoin('produtos', 'produtos.id', '=', $itemsTable . '.produto_id')
            ->where($salesTable . '.empresa_id', $empresaId)
            ->whereBetween($salesTable . '.' . $dateColumn, [$start, $end])
            ->selectRaw('SUM(COALESCE(' . $itemsTable . '.quantidade, 0) * COALESCE(NULLIF(' . $itemsTable . '.valor_custo, 0), produtos.valor_compra, 0)) as total');

        if ($localId !== 'todos' && Schema::hasColumn($salesTable, 'filial_id')) {
            if ($localId === '-1') {
                $query->whereNull($salesTable . '.filial_id');
            } else {
                $query->where(function ($q) use ($salesTable, $localId) {
                    $q->where($salesTable . '.filial_id', $localId)
                        ->orWhereNull($salesTable . '.filial_id');
                });
            }
        }

        return (float) ($query->value('total') ?? 0);
    }

    private function getExpenseForPeriod($empresaId, $localId, Carbon $start, Carbon $end): float
    {
        return (float) $this->buildContaPagarQuery($empresaId, $localId)
            ->whereBetween('data_vencimento', [$start->toDateString(), $end->toDateString()])
            ->sum(DB::raw('CASE WHEN COALESCE(valor_integral, 0) - COALESCE(valor_pago, 0) > 0 THEN COALESCE(valor_integral, 0) - COALESCE(valor_pago, 0) ELSE 0 END'));
    }

    private function getReceivedForPeriod($empresaId, $localId, Carbon $start, Carbon $end): float
    {
        return (float) $this->buildContaReceberQuery($empresaId, $localId)
            ->where('status', true)
            ->whereBetween('data_recebimento', [$start->toDateString(), $end->toDateString()])
            ->sum('valor_recebido');
    }

    private function getPaidForPeriod($empresaId, $localId, Carbon $start, Carbon $end): float
    {
        return (float) $this->buildContaPagarQuery($empresaId, $localId)
            ->where('status', true)
            ->whereBetween('data_pagamento', [$start->toDateString(), $end->toDateString()])
            ->sum('valor_pago');
    }

    private function resolvePeriod(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth()->startOfDay();
        $end = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        return [
            'start' => $start,
            'end' => $end,
            'label' => $start->format('m/Y'),
        ];
    }

    private function resolveClientLabel($sale): string
    {
        if (method_exists($sale, 'relationLoaded') && $sale->relationLoaded('cliente') && $sale->cliente) {
            return $sale->cliente->razao_social ?: ($sale->cliente->nome_fantasia ?: 'Cliente sem nome');
        }

        if (!empty($sale->nome)) {
            return $sale->nome;
        }

        return 'Consumidor final';
    }

    private function resolvePaymentLabel($tipoPagamento, $formaPagamento = null): string
    {
        if (!empty($tipoPagamento) && isset(Venda::tiposPagamento()[$tipoPagamento])) {
            return Venda::tiposPagamento()[$tipoPagamento];
        }

        if (!empty($tipoPagamento) && isset(VendaCaixa::tiposPagamento()[$tipoPagamento])) {
            return VendaCaixa::tiposPagamento()[$tipoPagamento];
        }

        if (!empty($formaPagamento)) {
            return ucwords(str_replace('_', ' ', (string) $formaPagamento));
        }

        return 'Não identificado';
    }

    private function sumSales(string $modelClass, $empresaId, $localId, $inicio = null, $fim = null, ?string $dateColumn = null): float
    {
        $query = $this->buildValidSalesQuery($modelClass, $empresaId, $localId, $inicio, $fim, $dateColumn);

        return (float) $query->sum('valor_total');
    }

    private function countSales(string $modelClass, $empresaId, $localId, $inicio, $fim, ?string $dateColumn = null): int
    {
        $query = $this->buildValidSalesQuery($modelClass, $empresaId, $localId, $inicio, $fim, $dateColumn);

        return (int) $query->count();
    }

    private function buildValidSalesQuery(string $modelClass, $empresaId, $localId, $inicio = null, $fim = null, ?string $dateColumn = null): Builder
    {
        /** @var Builder $query */
        $query = $modelClass::query()->where('empresa_id', $empresaId);
        $this->applyFilialFilter($query, $localId, true);
        $this->applySalesValidityFilters($query, new $modelClass);

        if ($inicio && $fim) {
            $query->whereBetween($dateColumn ?: $this->resolveDateColumn((new $modelClass)->getTable()), [$inicio, $fim]);
        }

        return $query;
    }

    private function applySalesValidityFilters(Builder $query, $model): void
    {
        $table = $model->getTable();

        if (Schema::hasColumn($table, 'estado_emissao')) {
            $query->where(function ($q) {
                $q->whereNull('estado_emissao')
                    ->orWhereRaw('LOWER(TRIM(estado_emissao)) NOT IN (?, ?)', ['cancelado', 'rejeitado']);
            });
        }

        if (Schema::hasColumn($table, 'rascunho')) {
            $query->where(function ($q) {
                $q->whereNull('rascunho')->orWhere('rascunho', false);
            });
        }

        if (Schema::hasColumn($table, 'consignado')) {
            $query->where(function ($q) {
                $q->whereNull('consignado')->orWhere('consignado', false);
            });
        }
    }

    private function sumOpenReceivables($empresaId, $localId): float
    {
        return (float) $this->buildContaReceberQuery($empresaId, $localId)
            ->where('status', false)
            ->sum(DB::raw('CASE WHEN COALESCE(valor_integral, 0) - COALESCE(valor_recebido, 0) > 0 THEN COALESCE(valor_integral, 0) - COALESCE(valor_recebido, 0) ELSE 0 END'));
    }

    private function sumOpenPayables($empresaId, $localId): float
    {
        return (float) $this->buildContaPagarQuery($empresaId, $localId)
            ->where('status', false)
            ->sum(DB::raw('CASE WHEN COALESCE(valor_integral, 0) - COALESCE(valor_pago, 0) > 0 THEN COALESCE(valor_integral, 0) - COALESCE(valor_pago, 0) ELSE 0 END'));
    }

    private function buildContaReceberQuery($empresaId, $localId): Builder
    {
        $query = ContaReceber::query()->where('empresa_id', $empresaId);
        $this->applyFilialFilter($query, $localId);
        return $query;
    }

    private function buildContaPagarQuery($empresaId, $localId): Builder
    {
        $query = ContaPagar::query()->where('empresa_id', $empresaId);
        $this->applyFilialFilter($query, $localId);
        return $query;
    }

    private function countProducts($empresaId, $localId): int
    {
        $query = Produto::query()->where('empresa_id', $empresaId);

        if (Schema::hasColumn('produtos', 'inativo')) {
            $query->where(function ($q) {
                $q->whereNull('inativo')->orWhere('inativo', false);
            });
        }

        $query->when($localId !== 'todos', function ($query) use ($localId) {
            $query->where(function ($q) use ($localId) {
                $q->where('locais', '=', $localId)
                    ->orWhere('locais', 'like', '%"' . $localId . '"%')
                    ->orWhere('locais', 'like', '%[' . $localId . ']%')
                    ->orWhere('locais', 'like', $localId . ',%')
                    ->orWhere('locais', 'like', '%,' . $localId . ',%')
                    ->orWhere('locais', 'like', '%,' . $localId);

                if (Schema::hasColumn('produtos', 'filial_id')) {
                    if ($localId === '-1') {
                        $q->orWhereNull('filial_id');
                    } else {
                        $q->orWhere('filial_id', $localId);
                    }
                }
            });
        });

        return (int) $query->count();
    }

    private function applyFilialFilter(Builder $query, $localId, bool $includeNullFallback = false): void
    {
        if ($localId === 'todos') {
            return;
        }

        $table = $query->getModel()->getTable();
        if (!Schema::hasColumn($table, 'filial_id')) {
            return;
        }

        if ($localId === '-1') {
            $query->whereNull('filial_id');
            return;
        }

        if ($includeNullFallback) {
            $query->where(function ($q) use ($localId) {
                $q->where('filial_id', $localId)
                    ->orWhereNull('filial_id');
            });

            return;
        }

        $query->where('filial_id', $localId);
    }

    private function resolveDateColumn(string $table): string
    {
        return Schema::hasColumn($table, 'data_registro') ? 'data_registro' : 'created_at';
    }
}
