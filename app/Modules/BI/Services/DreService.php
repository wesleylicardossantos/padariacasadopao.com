<?php

namespace App\Modules\BI\Services;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DreService
{
    protected DashboardService $dashboard;

    public function __construct(DashboardService $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    public function summary(int $empresaId, $filialId = 'todos', ?int $year = null, ?int $month = null): array
    {
        return Cache::remember(sprintf('enterprise:bi:dre:%s:%s:%s:%s', $empresaId, (string) $filialId, (string) ($year ?? now()->year), (string) ($month ?? now()->month)), now()->addSeconds(120), function () use ($empresaId, $filialId, $year, $month) {
            $data = $this->dashboard->getDreSummary($empresaId, $filialId, $year, $month);
            Log::channel('bi')->info('DRE consolidada gerada', [
                'empresa_id' => $empresaId,
                'filial_id' => $filialId,
                'ano' => $year,
                'mes' => $month,
            ]);
            return $data;
        });
    }
}
