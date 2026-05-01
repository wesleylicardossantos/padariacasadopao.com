<?php

namespace App\Modules\Financeiro\Repositories;

use App\Models\ContaPagar;
use App\Modules\Financeiro\Data\FinanceFilterData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PayableRepository
{
    public function paginatedIndex(int $empresaId, FinanceFilterData $filter, int $perPage): LengthAwarePaginator
    {
        return $this->filteredQuery($empresaId, $filter)
            ->orderBy('data_vencimento', 'asc')
            ->paginate($perPage);
    }

    public function filteredQuery(int $empresaId, FinanceFilterData $filter): Builder
    {
        return ContaPagar::query()
            ->where('empresa_id', $empresaId)
            ->when(! empty($filter->startDate), fn (Builder $query) => $query->where($filter->typeSearch ?? 'data_vencimento', '>=', $filter->startDate))
            ->when(! empty($filter->endDate), fn (Builder $query) => $query->where($filter->typeSearch ?? 'data_vencimento', '<=', $filter->endDate))
            ->when($filter->partyId !== null, fn (Builder $query) => $query->where('fornecedor_id', $filter->partyId))
            ->when($filter->hasStatusFilter(), fn (Builder $query) => $query->where('status', $filter->status))
            ->when($filter->filialId !== 'todos', fn (Builder $query) => $query->where('filial_id', $filter->normalizedFilialId()));
    }
}
