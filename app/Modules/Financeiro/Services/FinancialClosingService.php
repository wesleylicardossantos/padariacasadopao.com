<?php

namespace App\Modules\Financeiro\Services;

use App\Models\ContaPagar;
use App\Models\ContaReceber;

class FinancialClosingService
{
    public function monthlyClosure(int $empresaId, $filialId = 'todos', ?int $year = null, ?int $month = null): array
    {
        $year ??= (int) now()->year;
        $month ??= (int) now()->month;
        $start = now()->setDate($year, $month, 1)->startOfMonth()->toDateString();
        $end = now()->setDate($year, $month, 1)->endOfMonth()->toDateString();

        $receivable = ContaReceber::query()->where('empresa_id', $empresaId);
        $payable = ContaPagar::query()->where('empresa_id', $empresaId);

        if ($filialId !== 'todos' && $filialId !== null && $filialId !== '') {
            $receivable->where('filial_id', $filialId === -1 ? null : $filialId);
            $payable->where('filial_id', $filialId === -1 ? null : $filialId);
        }

        $receivableCompetence = (clone $receivable)->whereBetween('data_vencimento', [$start, $end]);
        $payableCompetence = (clone $payable)->whereBetween('data_vencimento', [$start, $end]);

        $recebido = (float) (clone $receivableCompetence)->where('status', true)->sum('valor_recebido');
        $pago = (float) (clone $payableCompetence)->where('status', true)->sum('valor_pago');

        return [
            'competencia' => sprintf('%02d/%d', $month, $year),
            'periodo' => ['inicio' => $start, 'fim' => $end],
            'receber_competencia' => (float) (clone $receivableCompetence)->sum('valor_integral'),
            'pagar_competencia' => (float) (clone $payableCompetence)->sum('valor_integral'),
            'recebido_competencia' => $recebido,
            'pago_competencia' => $pago,
            'inadimplencia_competencia' => (float) (clone $receivableCompetence)->where('status', false)->whereDate('data_vencimento', '<', now()->toDateString())->sum('valor_integral'),
            'backlog_pagar' => (float) (clone $payable)->where('status', false)->sum('valor_integral'),
            'backlog_receber' => (float) (clone $receivable)->where('status', false)->sum('valor_integral'),
            'resultado_caixa' => round($recebido - $pago, 2),
        ];
    }
}
