<?php

namespace App\Modules\RH\Http\Controllers\Concerns;

use App\Modules\RH\Support\RHContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait InteractsWithRH
{
    protected function empresaId(?Request $request = null): int
    {
        return RHContext::empresaId($request);
    }

    protected function perPage(): int
    {
        return (int) env('PAGINACAO', 20);
    }

    protected function scopeEmpresa(Builder $query, ?Request $request = null, string $table = ''): Builder
    {
        return RHContext::applyEmpresaScope($query, $this->empresaId($request), $table);
    }
}
