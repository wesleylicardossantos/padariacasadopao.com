<?php

namespace App\Modules\RH\Application\Queries;

use App\Models\RHCompetencia;
use App\Modules\RH\Support\RHContext;
use Illuminate\Database\Eloquent\Builder;

final class PayrollCompetenciaQuery
{
    public function make(?int $empresaId = null, mixed $filialId = null): Builder
    {
        $query = RHCompetencia::query()->orderByDesc('ano')->orderByDesc('mes');
        RHContext::applyTenantScope($query, $empresaId, $filialId, 'rh_competencias');

        return $query;
    }
}
