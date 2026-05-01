<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfPossible('pdv_offline_syncs', ['empresa_id', 'status'], 'pdv_sync_empresa_status_idx');
        $this->addIndexIfPossible('stock_movements', ['empresa_id', 'produto_id'], 'stock_movements_empresa_produto_idx');
        $this->addIndexIfPossible('financial_audits', ['empresa_id', 'acao'], 'financial_audits_empresa_acao_idx');
        $this->addIndexIfPossible('commercial_audits', ['empresa_id', 'acao'], 'commercial_audits_empresa_acao_idx');
        $this->addIndexIfPossible('fiscal_documents', ['empresa_id', 'status'], 'fiscal_documents_empresa_status_idx');
        $this->addIndexIfPossible('fiscal_audits', ['empresa_id', 'acao'], 'fiscal_audits_empresa_acao_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('pdv_offline_syncs', 'pdv_sync_empresa_status_idx');
        $this->dropIndexIfExists('stock_movements', 'stock_movements_empresa_produto_idx');
        $this->dropIndexIfExists('financial_audits', 'financial_audits_empresa_acao_idx');
        $this->dropIndexIfExists('commercial_audits', 'commercial_audits_empresa_acao_idx');
        $this->dropIndexIfExists('fiscal_documents', 'fiscal_documents_empresa_status_idx');
        $this->dropIndexIfExists('fiscal_audits', 'fiscal_audits_empresa_acao_idx');
    }

    protected function addIndexIfPossible(string $table, array $columns, string $indexName): void
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

    protected function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection()->getDriverName();

        if ($connection === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('{$table}')");
            foreach ($rows as $row) {
                if (($row->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return !empty($rows);
    }
};
