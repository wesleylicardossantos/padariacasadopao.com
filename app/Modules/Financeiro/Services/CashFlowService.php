<?php

namespace App\Modules\Financeiro\Services;

class CashFlowService
{
    public function __construct(
        protected ReceivableService $receivableService,
        protected PayableService $payableService,
        protected FinancialCacheService $cacheService,
    ) {
    }

    public function projection(int $empresaId, $filialId = 'todos', int $months = 6): array
    {
        return $this->cacheService->remember('cashflow', $empresaId, $filialId, now()->addSeconds(120), function () use ($empresaId, $filialId, $months) {
            $entradas = $this->receivableService->monthlyForecast($empresaId, $filialId, $months);
            $saidas = $this->payableService->monthlyForecast($empresaId, $filialId, $months);
            $series = [];
            $saldoAcumulado = 0.0;

            foreach ($entradas as $index => $entrada) {
                $saida = $saidas[$index] ?? ['valor' => 0.0, 'quantidade' => 0];
                $saldoMes = (float) $entrada['valor'] - (float) $saida['valor'];
                $saldoAcumulado += $saldoMes;
                $series[] = [
                    'periodo' => $entrada['periodo'],
                    'label' => $entrada['label'],
                    'entradas_previstas' => (float) $entrada['valor'],
                    'saidas_previstas' => (float) $saida['valor'],
                    'saldo_mes' => (float) $saldoMes,
                    'saldo_acumulado' => (float) $saldoAcumulado,
                    'qtd_receber' => (int) $entrada['quantidade'],
                    'qtd_pagar' => (int) $saida['quantidade'],
                ];
            }

            return [
                'entradas_previstas' => array_sum(array_column($series, 'entradas_previstas')),
                'saidas_previstas' => array_sum(array_column($series, 'saidas_previstas')),
                'saldo_previsto' => array_sum(array_column($series, 'saldo_mes')),
                'series' => $series,
            ];
        });
    }
}
