<?php

namespace App\Modules\Comercial\Services;

use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Database\Eloquent\Builder;

class SalesService
{
    public function baseQuery(string $model, int $empresaId, $filialId = 'todos'): Builder
    {
        /** @var Builder $query */
        $query = $model::query()->where('empresa_id', $empresaId);
        if ($filialId !== 'todos' && $filialId !== null && $this->hasColumn($model, 'filial_id')) {
            $query->where('filial_id', $filialId === -1 ? null : $filialId);
        }

        return $query;
    }

    public function totalPeriod(int $empresaId, $filialId, \DateTimeInterface $start, \DateTimeInterface $end): float
    {
        return (float) $this->sumForModel(Venda::class, $empresaId, $filialId, $start, $end)
            + (float) $this->sumForModel(VendaCaixa::class, $empresaId, $filialId, $start, $end);
    }

    public function countPeriod(int $empresaId, $filialId, \DateTimeInterface $start, \DateTimeInterface $end): int
    {
        return (int) $this->countForModel(Venda::class, $empresaId, $filialId, $start, $end)
            + (int) $this->countForModel(VendaCaixa::class, $empresaId, $filialId, $start, $end);
    }

    private function sumForModel(string $model, int $empresaId, $filialId, \DateTimeInterface $start, \DateTimeInterface $end): float
    {
        $query = $this->baseQuery($model, $empresaId, $filialId);
        $dateColumn = $this->resolveDateColumn($model);
        $query->whereBetween($dateColumn, [$start, $end]);

        $column = $this->hasColumn($model, 'valor_total') ? 'valor_total' : ($this->hasColumn($model, 'valor') ? 'valor' : 'id');
        return (float) $query->sum($column);
    }

    private function countForModel(string $model, int $empresaId, $filialId, \DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $query = $this->baseQuery($model, $empresaId, $filialId);
        $dateColumn = $this->resolveDateColumn($model);
        $query->whereBetween($dateColumn, [$start, $end]);
        return (int) $query->count();
    }

    private function resolveDateColumn(string $model): string
    {
        $instance = new $model();
        $table = $instance->getTable();
        foreach (['created_at', 'data_registro', 'data'] as $candidate) {
            if ($this->hasColumn($model, $candidate)) {
                return $candidate;
            }
        }

        return $instance->getKeyName();
    }

    private function hasColumn(string $model, string $column): bool
    {
        $instance = new $model();
        return \Illuminate\Support\Facades\Schema::hasColumn($instance->getTable(), $column);
    }
}
