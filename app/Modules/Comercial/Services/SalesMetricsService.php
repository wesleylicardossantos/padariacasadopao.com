<?php

namespace App\Modules\Comercial\Services;

class SalesMetricsService
{
    public function __construct(private readonly SalesService $sales)
    {
    }

    public function currentMonth(int $empresaId, $filialId = 'todos'): array
    {
        $start = now()->startOfMonth()->startOfDay();
        $end = now()->endOfMonth()->endOfDay();
        $total = $this->sales->totalPeriod($empresaId, $filialId, $start, $end);
        $count = $this->sales->countPeriod($empresaId, $filialId, $start, $end);

        return [
            'vendas_mes' => $total,
            'qtd_vendas_mes' => $count,
            'ticket_medio' => $count > 0 ? round($total / $count, 2) : 0.0,
        ];
    }

    public function today(int $empresaId, $filialId = 'todos'): array
    {
        $start = now()->startOfDay();
        $end = now()->endOfDay();

        return [
            'vendas_hoje' => $this->sales->totalPeriod($empresaId, $filialId, $start, $end),
            'qtd_vendas_hoje' => $this->sales->countPeriod($empresaId, $filialId, $start, $end),
        ];
    }
}
