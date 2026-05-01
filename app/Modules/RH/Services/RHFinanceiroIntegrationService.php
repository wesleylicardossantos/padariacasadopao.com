<?php

namespace App\Modules\RH\Services;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use App\Modules\RH\Support\ResolveEmpresaId;

class RHFinanceiroIntegrationService
{
    public function __construct(private RHAnalyticsModuleService $analytics)
    {
    }

    public function competencia(int $empresaId, int $mes, int $ano): array
    {
        $empresaId = $empresaId > 0 ? $empresaId : ResolveEmpresaId::fromRequest();
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));
        $custosRh = $this->analytics->calcularCustosCompetencia($empresaId, $mes, $ano);

        $receitaPrevista = $this->sumReceber($empresaId, $inicio, $fim, false);
        $receitaRecebida = $this->sumReceber($empresaId, $inicio, $fim, true);
        $despesaPrevista = $this->sumPagar($empresaId, $inicio, $fim, false);
        $despesaPaga = $this->sumPagar($empresaId, $inicio, $fim, true);
        $despesaSemRh = max($despesaPrevista - ($custosRh['total_rh'] ?? 0), 0);
        $resultadoPrevisto = $receitaPrevista - $despesaPrevista;
        $resultadoCaixa = $receitaRecebida - $despesaPaga;
        $pesoFolhaReceita = $receitaPrevista > 0 ? (($custosRh['total_rh'] ?? 0) / $receitaPrevista) * 100 : 0;
        $pesoFolhaCaixa = $receitaRecebida > 0 ? (($custosRh['folha_liquida'] ?? 0) / $receitaRecebida) * 100 : 0;
        $capitalComprometido = $receitaPrevista > 0 ? (($despesaPrevista + ($custosRh['total_rh'] ?? 0)) / $receitaPrevista) * 100 : 0;
        $coberturaFolha = ($custosRh['folha_liquida'] ?? 0) > 0 ? $receitaRecebida / ($custosRh['folha_liquida'] ?? 1) : 0;

        return [
            'inicio' => $inicio,
            'fim' => $fim,
            'receitaPrevista' => round($receitaPrevista, 2),
            'receitaRecebida' => round($receitaRecebida, 2),
            'despesaPrevista' => round($despesaPrevista, 2),
            'despesaPaga' => round($despesaPaga, 2),
            'despesaSemRh' => round($despesaSemRh, 2),
            'resultadoPrevisto' => round($resultadoPrevisto, 2),
            'resultadoCaixa' => round($resultadoCaixa, 2),
            'pesoFolhaReceita' => round($pesoFolhaReceita, 2),
            'pesoFolhaCaixa' => round($pesoFolhaCaixa, 2),
            'capitalComprometido' => round($capitalComprometido, 2),
            'coberturaFolha' => round($coberturaFolha, 2),
            'custosRh' => $custosRh,
            'categoriasPagar' => $this->categoriasPagar($empresaId, $inicio, $fim),
            'categoriasReceber' => $this->categoriasReceber($empresaId, $inicio, $fim),
        ];
    }

    public function serieMensal(int $empresaId, int $mes, int $ano, int $quantidade = 6): array
    {
        $serie = [];
        for ($i = $quantidade - 1; $i >= 0; $i--) {
            $ref = strtotime(sprintf('%04d-%02d-01', $ano, $mes) . " -{$i} month");
            $m = (int) date('m', $ref);
            $a = (int) date('Y', $ref);
            $snapshot = $this->competencia($empresaId, $m, $a);
            $serie[] = [
                'mes' => $m,
                'ano' => $a,
                'label' => str_pad((string) $m, 2, '0', STR_PAD_LEFT) . '/' . $a,
                'receita_prevista' => $snapshot['receitaPrevista'],
                'receita_recebida' => $snapshot['receitaRecebida'],
                'rh_total' => $snapshot['custosRh']['total_rh'] ?? 0,
                'folha_liquida' => $snapshot['custosRh']['folha_liquida'] ?? 0,
                'resultado_previsto' => $snapshot['resultadoPrevisto'],
                'resultado_caixa' => $snapshot['resultadoCaixa'],
                'peso_folha' => $snapshot['pesoFolhaReceita'],
            ];
        }

        return $serie;
    }

    private function categoriasPagar(int $empresaId, string $inicio, string $fim): array
    {
        if (!Schema::hasTable('conta_pagars')) {
            return [];
        }

        return ContaPagar::with('categoria')
            ->where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->get()
            ->groupBy(function ($item) {
                return trim((string) optional($item->categoria)->nome) ?: 'Sem categoria';
            })
            ->map(function (Collection $items, string $categoria) {
                return [
                    'categoria' => $categoria,
                    'valor' => round((float) $items->sum('valor_integral'), 2),
                    'pago' => round((float) $items->where('status', 1)->sum('valor_pago'), 2),
                    'quantidade' => $items->count(),
                ];
            })
            ->sortByDesc('valor')
            ->take(8)
            ->values()
            ->all();
    }

    private function categoriasReceber(int $empresaId, string $inicio, string $fim): array
    {
        if (!Schema::hasTable('conta_recebers')) {
            return [];
        }

        return ContaReceber::with('categoria')
            ->where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->get()
            ->groupBy(function ($item) {
                return trim((string) optional($item->categoria)->nome) ?: 'Sem categoria';
            })
            ->map(function (Collection $items, string $categoria) {
                return [
                    'categoria' => $categoria,
                    'valor' => round((float) $items->sum('valor_integral'), 2),
                    'recebido' => round((float) $items->where('status', 1)->sum('valor_recebido'), 2),
                    'quantidade' => $items->count(),
                ];
            })
            ->sortByDesc('valor')
            ->take(8)
            ->values()
            ->all();
    }

    private function sumReceber(int $empresaId, string $inicio, string $fim, bool $somenteRecebido): float
    {
        if (!Schema::hasTable('conta_recebers')) {
            return 0.0;
        }

        $query = ContaReceber::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio, $fim]);

        if ($somenteRecebido) {
            return (float) $query->where('status', 1)->sum('valor_recebido');
        }

        return (float) $query->sum('valor_integral');
    }

    private function sumPagar(int $empresaId, string $inicio, string $fim, bool $somentePago): float
    {
        if (!Schema::hasTable('conta_pagars')) {
            return 0.0;
        }

        $query = ContaPagar::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio, $fim]);

        if ($somentePago) {
            return (float) $query->where('status', 1)->sum('valor_pago');
        }

        return (float) $query->sum('valor_integral');
    }
}
