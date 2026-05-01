<?php

namespace App\Modules\RH\Application\Queries;

use App\Models\Funcionario;
use App\Modules\RH\Support\RHContext;
use Illuminate\Database\Eloquent\Builder;

final class ActiveEmployeesQuery
{
    public function make(?int $empresaId = null, mixed $filialId = null): Builder
    {
        $query = Funcionario::query()->somenteAtivos();
        RHContext::applyTenantScope($query, $empresaId, $filialId, 'funcionarios');

        return $query;
    }
}
