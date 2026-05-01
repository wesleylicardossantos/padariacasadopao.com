<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfPossible('sangria_caixas', ['empresa_id', 'data_registro'], 'sangria_caixas_empresa_data_registro_idx');
        $this->addIndexIfPossible('sangria_caixas', ['empresa_id', 'created_at'], 'sangria_caixas_empresa_created_at_idx');
        $this->addIndexIfPossible('clientes', ['empresa_id', 'cpf_cnpj'], 'clientes_empresa_cpf_cnpj_idx');
        $this->addIndexIfPossible('clientes', ['empresa_id', 'razao_social'], 'clientes_empresa_razao_social_idx');
        $this->addIndexIfPossible('venda_caixas', ['empresa_id', 'data_registro', 'deleted_at'], 'venda_caixas_empresa_data_deleted_idx');
        $this->addIndexIfPossible('venda_caixas', ['empresa_id', 'tipo_pagamento'], 'venda_caixas_empresa_tipo_pagamento_idx');
        $this->addIndexIfPossible('vendas', ['empresa_id', 'data_registro'], 'vendas_empresa_data_registro_idx');
        $this->addIndexIfPossible('vendas', ['empresa_id', 'estado_emissao'], 'vendas_empresa_estado_emissao_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('sangria_caixas', 'sangria_caixas_empresa_data_registro_idx');
        $this->dropIndexIfExists('sangria_caixas', 'sangria_caixas_empresa_created_at_idx');
        $this->dropIndexIfExists('clientes', 'clientes_empresa_cpf_cnpj_idx');
        $this->dropIndexIfExists('clientes', 'clientes_empresa_razao_social_idx');
        $this->dropIndexIfExists('venda_caixas', 'venda_caixas_empresa_data_deleted_idx');
        $this->dropIndexIfExists('venda_caixas', 'venda_caixas_empresa_tipo_pagamento_idx');
        $this->dropIndexIfExists('vendas', 'vendas_empresa_data_registro_idx');
        $this->dropIndexIfExists('vendas', 'vendas_empresa_estado_emissao_idx');
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

        Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
            $tableBlueprint->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
            $tableBlueprint->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('{$table}')");
            foreach ($rows as $row) {
                if (($row->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        return !empty(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]));
    }
};
