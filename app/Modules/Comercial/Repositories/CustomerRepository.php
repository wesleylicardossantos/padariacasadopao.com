<?php

namespace App\Modules\Comercial\Repositories;

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CustomerRepository
{
    public function queryByEmpresa(int $empresaId): Builder
    {
        return Cliente::query()->where('empresa_id', $empresaId);
    }

    public function paginate(int $empresaId, ?string $search = null, ?string $documento = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->queryByEmpresa($empresaId)
            ->when($search, function (Builder $query, string $search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('razao_social', 'like', "%{$search}%")
                        ->orWhere('nome_fantasia', 'like', "%{$search}%")
                        ->orWhere('telefone', 'like', "%{$search}%");
                });
            })
            ->when($documento, fn (Builder $query, string $documento) => $query->where('cpf_cnpj', $documento))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findByEmpresa(int $empresaId, int $customerId): ?Cliente
    {
        return $this->queryByEmpresa($empresaId)->find($customerId);
    }

    public function create(array $attributes): Cliente
    {
        return Cliente::query()->create($attributes);
    }

    public function update(Cliente $customer, array $attributes): Cliente
    {
        $customer->fill($attributes);
        $customer->save();

        return $customer->fresh();
    }

    public function upsert(int $empresaId, array $attributes): Cliente
    {
        $customerId = (int) ($attributes['id'] ?? 0);
        unset($attributes['id']);

        if ($customerId > 0 && ($customer = $this->findByEmpresa($empresaId, $customerId))) {
            return $this->update($customer, $attributes + ['empresa_id' => $empresaId]);
        }

        return $this->create($attributes + ['empresa_id' => $empresaId]);
    }
}
