<?php

namespace App\Modules\BI\Services;

class KpiService
{
    protected DashboardQueryService $dashboard;

    public function __construct(DashboardQueryService $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    public function summary(int $empresaId, $filialId = 'todos', ?int $year = null, ?int $month = null): array
    {
        return [
            'cards' => $this->dashboard->snapshot($empresaId, $filialId),
            'series' => $this->dashboard->annualSeries($empresaId, $filialId, $year),
            'overview' => $this->dashboard->overview($empresaId, $filialId, $year, $month),
        ];
    }
}
