<?php

namespace App\Modules\RH\Application\Queries;

use App\Models\Funcionario;
use App\Modules\RH\Support\RHContext;
use Illuminate\Database\Eloquent\Builder;

final class ArchivedEmployeesQuery
{
    public function make(?int $empresaId = null, mixed $filialId = null): Builder
    {
        $query = Funcionario::query()->arquivoMorto();
        RHContext::applyTenantScope($query, $empresaId, $filialId, 'funcionarios');

        return $query;
    }
}
