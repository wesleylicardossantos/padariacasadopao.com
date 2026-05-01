<?php

namespace App\Modules\BI\Services;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardQueryService
{
    protected DashboardService $dashboard;

    public function __construct(DashboardService $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    public function snapshot(int $empresaId, $filialId = 'todos'): array
    {
        return Cache::remember(sprintf('enterprise:bi:snapshot:%s:%s', $empresaId, (string) $filialId), now()->addSeconds(60), function () use ($empresaId, $filialId) {
            $data = $this->dashboard->getCardsSnapshot($empresaId, $filialId);
            Log::channel('bi')->info('Snapshot BI gerado', ['empresa_id' => $empresaId, 'filial_id' => $filialId]);
            return $data;
        });
    }

    public function annualSeries(int $empresaId, $filialId = 'todos', ?int $year = null): array
    {
        return Cache::remember(sprintf('enterprise:bi:annual:%s:%s:%s', $empresaId, (string) $filialId, (string) ($year ?? now()->year)), now()->addSeconds(120), function () use ($empresaId, $filialId, $year) {
            return $this->dashboard->getAnnualSalesSeries($empresaId, $filialId, $year);
        });
    }

    public function overview(int $empresaId, $filialId = 'todos', ?int $year = null, ?int $month = null): array
    {
        return Cache::remember(sprintf('enterprise:bi:overview:%s:%s:%s:%s', $empresaId, (string) $filialId, (string) ($year ?? now()->year), (string) ($month ?? now()->month)), now()->addSeconds(90), function () use ($empresaId, $filialId, $year, $month) {
            return $this->dashboard->getBiOverview($empresaId, $filialId, $year, $month);
        });
    }
}
