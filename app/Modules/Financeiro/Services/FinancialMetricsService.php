<?php

namespace App\Modules\Financeiro\Services;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Illuminate\Support\Facades\Log;

class FinancialMetricsService
{
    public function __construct(
        protected ReceivableService $receivableService,
        protected PayableService $payableService,
        protected FinancialCacheService $cacheService,
    ) {
    }

    public function snapshot(int $empresaId, $filialId = 'todos'): array
    {
        return $this->cacheService->remember('snapshot', $empresaId, $filialId, now()->addSeconds(60), function () use ($empresaId, $filialId) {
            $receber = $this->receivableService->summary($empresaId, $filialId);
            $pagar = $this->payableService->summary($empresaId, $filialId);
            $saldoPrevisto = round(($receber['pendente_valor'] ?? 0) - ($pagar['pendente_valor'] ?? 0), 2);

            $payload = [
                'contas_receber' => $receber,
                'contas_pagar' => $pagar,
                'saldo_previsto' => $saldoPrevisto,
                'risco_caixa' => $saldoPrevisto < 0 ? 'alto' : ($saldoPrevisto < (($receber['pendente_valor'] ?? 0) * 0.15) ? 'moderado' : 'controlado'),
            ];

            Log::channel('financeiro')->info('Snapshot financeiro gerado', [
                'empresa_id' => $empresaId,
                'filial_id' => $filialId,
                'saldo_previsto' => $saldoPrevisto,
            ]);

            return $payload;
        });
    }

    public function aging(int $empresaId, $filialId = 'todos'): array
    {
        return $this->cacheService->remember('aging', $empresaId, $filialId, now()->addSeconds(90), function () use ($empresaId, $filialId) {
            $hoje = now()->toDateString();
            $ate7 = now()->copy()->addDays(7)->toDateString();
            $de8 = now()->copy()->addDays(8)->toDateString();
            $ate30 = now()->copy()->addDays(30)->toDateString();

            $receber = $this->scopedOpenEntries(ContaReceber::query(), $empresaId, $filialId)
                ->selectRaw("SUM(CASE WHEN data_vencimento < ? THEN valor_integral ELSE 0 END) as vencido", [$hoje])
                ->selectRaw("SUM(CASE WHEN data_vencimento BETWEEN ? AND ? THEN valor_integral ELSE 0 END) as ate7", [$hoje, $ate7])
                ->selectRaw("SUM(CASE WHEN data_vencimento BETWEEN ? AND ? THEN valor_integral ELSE 0 END) as de8a30", [$de8, $ate30])
                ->first();

            $pagar = $this->scopedOpenEntries(ContaPagar::query(), $empresaId, $filialId)
                ->selectRaw("SUM(CASE WHEN data_vencimento < ? THEN valor_integral ELSE 0 END) as vencido", [$hoje])
                ->selectRaw("SUM(CASE WHEN data_vencimento BETWEEN ? AND ? THEN valor_integral ELSE 0 END) as ate7", [$hoje, $ate7])
                ->selectRaw("SUM(CASE WHEN data_vencimento BETWEEN ? AND ? THEN valor_integral ELSE 0 END) as de8a30", [$de8, $ate30])
                ->first();

            return [
                'receber_vencido' => (float) ($receber->vencido ?? 0),
                'receber_ate_7' => (float) ($receber->ate7 ?? 0),
                'receber_8_30' => (float) ($receber->de8a30 ?? 0),
                'pagar_vencido' => (float) ($pagar->vencido ?? 0),
                'pagar_ate_7' => (float) ($pagar->ate7 ?? 0),
                'pagar_8_30' => (float) ($pagar->de8a30 ?? 0),
            ];
        });
    }

    public function overview(int $empresaId, $filialId = 'todos'): array
    {
        return $this->cacheService->remember('overview', $empresaId, $filialId, now()->addSeconds(90), function () use ($empresaId, $filialId) {
            return [
                'snapshot' => $this->snapshot($empresaId, $filialId),
                'aging' => $this->aging($empresaId, $filialId),
                'forecast_receber' => $this->receivableService->monthlyForecast($empresaId, $filialId),
                'forecast_pagar' => $this->payableService->monthlyForecast($empresaId, $filialId),
                'top_devedores' => $this->receivableService->topDebtors($empresaId, $filialId),
                'top_fornecedores' => $this->payableService->topSuppliers($empresaId, $filialId),
            ];
        });
    }

    private function scopedOpenEntries($query, int $empresaId, $filialId)
    {
        $query->where('empresa_id', $empresaId)->where('status', false);

        if ($filialId !== 'todos' && $filialId !== null && $filialId !== '') {
            $query->where('filial_id', $filialId === -1 ? null : $filialId);
        }

        return $query;
    }
}
