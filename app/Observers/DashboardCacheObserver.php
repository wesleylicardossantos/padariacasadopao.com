<?php

namespace App\Observers;

use App\Services\DashboardService;

class DashboardCacheObserver
{
    public function created($model): void
    {
        $this->refreshDashboard($model);
    }

    public function updated($model): void
    {
        $this->refreshDashboard($model);
    }

    public function deleted($model): void
    {
        $this->refreshDashboard($model);
    }

    public function restored($model): void
    {
        $this->refreshDashboard($model);
    }

    private function refreshDashboard($model): void
    {
        $empresaId = data_get($model, 'empresa_id');
        if (empty($empresaId)) {
            return;
        }

        app(DashboardService::class)->bumpCacheVersion($empresaId);
    }
}
