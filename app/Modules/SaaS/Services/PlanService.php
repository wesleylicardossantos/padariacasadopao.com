<?php

namespace App\Modules\SaaS\Services;

use App\Models\Plano;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PlanService
{
    public function visiblePlans(): Collection
    {
        if (! Schema::hasTable('planos')) {
            return collect();
        }

        return Plano::query()->where('visivel', 1)->orderBy('valor')->get();
    }
}
