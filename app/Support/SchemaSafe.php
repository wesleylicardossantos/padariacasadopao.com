<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class SchemaSafe
{
    /** @var array<string, array<int, string>> */
    protected static array $columnsCache = [];

    public static function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public static function hasColumn(string $table, string $column): bool
    {
        return self::hasTable($table) && in_array($column, self::columns($table), true);
    }

    /** @return array<int, string> */
    public static function columns(string $table): array
    {
        if (!self::hasTable($table)) {
            return [];
        }

        return self::$columnsCache[$table] ??= Schema::getColumnListing($table);
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    public static function filter(string $table, array $data): array
    {
        $columns = self::columns($table);
        if ($columns === []) {
            return [];
        }

        return array_intersect_key($data, array_flip($columns));
    }

    public static function fillAndSave(object $model, array $data): bool
    {
        if (!method_exists($model, 'getTable') || !method_exists($model, 'fill') || !method_exists($model, 'save')) {
            return false;
        }

        $filtered = self::filter($model->getTable(), $data);
        $model->fill($filtered);

        return (bool) $model->save();
    }

    public static function applyEmpresaScope(Builder $query, int $empresaId, string $table, string $column = 'empresa_id'): Builder
    {
        if (!self::hasColumn($table, $column)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($empresaId, $column) {
            $q->whereNull($column);
            if ($empresaId > 0) {
                $q->orWhere($column, $empresaId);
            }
        });
    }
}
