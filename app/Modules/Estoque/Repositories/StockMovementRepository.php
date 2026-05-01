<?php

namespace App\Modules\Estoque\Repositories;

use App\Modules\Estoque\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StockMovementRepository
{
    public function create(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    public function query(int $empresaId, int $productId, ?int $filialId): Builder
    {
        return StockMovement::query()
            ->where('empresa_id', $empresaId)
            ->where('product_id', $productId)
            ->when($filialId !== null, fn (Builder $query) => $query->where('filial_id', $filialId), fn (Builder $query) => $query->whereNull('filial_id'));
    }

    public function latestBalance(int $empresaId, int $productId, ?int $filialId): float
    {
        return (float) ($this->query($empresaId, $productId, $filialId)
            ->latest('id')
            ->value('balance_after') ?? 0);
    }

    public function latestCost(int $empresaId, int $productId, ?int $filialId): ?float
    {
        $cost = $this->query($empresaId, $productId, $filialId)
            ->whereNotNull('unit_cost')
            ->latest('id')
            ->value('unit_cost');

        return $cost !== null ? (float) $cost : null;
    }

    public function listRecentByEmpresa(int $empresaId, int $limit = 50): Collection
    {
        return StockMovement::query()
            ->where('empresa_id', $empresaId)
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
