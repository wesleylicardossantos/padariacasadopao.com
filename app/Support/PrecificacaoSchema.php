<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class PrecificacaoSchema
{
    public static function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function hasColumn(string $table, string $column): bool
    {
        try {
            if (! Schema::hasTable($table)) {
                return false;
            }

            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
