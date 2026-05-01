<?php

namespace App\Modules\AI\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ForecastService
{
    public function monthlyRevenueForecast(int $empresaId): array
    {
        return Cache::remember("ai_forecast_monthly_revenue_{$empresaId}", 120, function () use ($empresaId) {
            $series = $this->salesSeries($empresaId, 90);
            $average = count($series) > 0 ? array_sum($series) / count($series) : 0.0;
            $trend = $this->simpleTrend($series);
            $projected30 = max(0, round(($average + $trend) * 30, 2));

            return [
                'empresa_id' => $empresaId,
                'window_days' => 90,
                'daily_average' => round($average, 2),
                'trend_per_day' => round($trend, 4),
                'projected_next_30_days' => $projected30,
                'confidence' => $this->confidence($series),
                'series' => $series,
            ];
        });
    }

    public function cashRisk(int $empresaId): array
    {
        return Cache::remember("ai_cash_risk_{$empresaId}", 120, function () use ($empresaId) {
            $receivable = $this->sumByTable('conta_recebers', $empresaId, ['valor_integral', 'valor']);
            $payable = $this->sumByTable('conta_pagars', $empresaId, ['valor_integral', 'valor']);
            $salesForecast = $this->monthlyRevenueForecast($empresaId)['projected_next_30_days'] ?? 0;
            $coverage = $payable > 0 ? round((($receivable + $salesForecast) / $payable), 2) : null;

            $risk = 'baixo';
            if ($coverage !== null && $coverage < 1) {
                $risk = 'alto';
            } elseif ($coverage !== null && $coverage < 1.4) {
                $risk = 'medio';
            }

            return [
                'empresa_id' => $empresaId,
                'receivable_open' => round($receivable, 2),
                'payable_open' => round($payable, 2),
                'sales_forecast_30d' => round($salesForecast, 2),
                'coverage_ratio' => $coverage,
                'risk' => $risk,
            ];
        });
    }

    private function salesSeries(int $empresaId, int $days): array
    {
        foreach (['vendas', 'venda_caixas'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $valueColumn = $this->firstExistingColumn($table, ['valor_total', 'valor', 'total']);
            $dateColumn = $this->firstExistingColumn($table, ['created_at', 'data_registro', 'data_emissao', 'data']);
            $empresaColumn = $this->firstExistingColumn($table, ['empresa_id']);

            if (! $valueColumn || ! $dateColumn || ! $empresaColumn) {
                continue;
            }

            $start = Carbon::now()->subDays($days - 1)->startOfDay();
            $rows = DB::table($table)
                ->selectRaw("DATE({$dateColumn}) as ref_date, COALESCE(SUM({$valueColumn}),0) as total")
                ->where($empresaColumn, $empresaId)
                ->where($dateColumn, '>=', $start)
                ->groupBy(DB::raw("DATE({$dateColumn})"))
                ->orderBy('ref_date')
                ->pluck('total', 'ref_date');

            $series = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $ref = Carbon::now()->subDays($i)->toDateString();
                $series[] = (float) ($rows[$ref] ?? 0);
            }

            return $series;
        }

        return [];
    }

    private function simpleTrend(array $series): float
    {
        $count = count($series);
        if ($count < 14) {
            return 0.0;
        }

        $first = array_slice($series, 0, (int) floor($count / 2));
        $last = array_slice($series, (int) floor($count / 2));
        $firstAvg = count($first) ? array_sum($first) / count($first) : 0;
        $lastAvg = count($last) ? array_sum($last) / count($last) : 0;

        return ($lastAvg - $firstAvg) / max($count / 2, 1);
    }

    private function confidence(array $series): string
    {
        if (count(array_filter($series, fn ($v) => $v > 0)) >= 45) {
            return 'alta';
        }

        if (count(array_filter($series, fn ($v) => $v > 0)) >= 20) {
            return 'media';
        }

        return 'baixa';
    }

    private function sumByTable(string $table, int $empresaId, array $valueCandidates): float
    {
        if (! Schema::hasTable($table)) {
            return 0.0;
        }

        $valueColumn = $this->firstExistingColumn($table, $valueCandidates);
        $empresaColumn = $this->firstExistingColumn($table, ['empresa_id']);
        $statusColumn = $this->firstExistingColumn($table, ['status', 'situacao']);

        if (! $valueColumn || ! $empresaColumn) {
            return 0.0;
        }

        $query = DB::table($table)->where($empresaColumn, $empresaId);
        if ($statusColumn) {
            $query->whereNotIn($statusColumn, ['quitado', 'pago', 1]);
        }

        return (float) $query->sum($valueColumn);
    }

    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }
}
