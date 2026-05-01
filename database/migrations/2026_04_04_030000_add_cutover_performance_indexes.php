<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfPossible('vendas', ['empresa_id', 'created_at'], 'vendas_empresa_created_idx');
        $this->addIndexIfPossible('orcamentos', ['empresa_id', 'created_at'], 'orcamentos_empresa_created_idx');
        $this->addIndexIfPossible('pedidos', ['empresa_id', 'created_at'], 'pedidos_empresa_created_idx');
        $this->addIndexIfPossible('clientes', ['empresa_id', 'updated_at'], 'clientes_empresa_updated_idx');
        $this->addIndexIfPossible('stock_write_audits', ['empresa_id', 'created_at'], 'stock_write_audits_empresa_created_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('vendas', 'vendas_empresa_created_idx');
        $this->dropIndexIfExists('orcamentos', 'orcamentos_empresa_created_idx');
        $this->dropIndexIfExists('pedidos', 'pedidos_empresa_created_idx');
        $this->dropIndexIfExists('clientes', 'clientes_empresa_updated_idx');
        $this->dropIndexIfExists('stock_write_audits', 'stock_write_audits_empresa_created_idx');
    }

    private function addIndexIfPossible(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = config('database.connections.' . config('database.default') . '.driver');
            if ($driver === 'sqlite') {
                $rows = DB::select("PRAGMA index_list('{$table}')");
                foreach ($rows as $row) {
                    if (((array) $row)['name'] === $indexName) {
                        return true;
                    }
                }
                return false;
            }

            $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return !empty($rows);
        } catch (Throwable) {
            return false;
        }
    }
};
