<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfPossible('rh_portal_perfis', ['empresa_id', 'ativo', 'nome'], 'rh_portal_perfis_empresa_ativo_nome_idx');
        $this->addIndexIfPossible('rh_portal_funcionarios', ['empresa_id', 'funcionario_id', 'ativo'], 'rh_portal_funcionarios_empresa_func_ativo_idx');
        $this->addIndexIfPossible('apuracao_mensals', ['empresa_id', 'funcionario_id', 'ano', 'mes'], 'apuracao_mensals_empresa_func_periodo_idx');
        $this->addIndexIfPossible('produtos', ['empresa_id', 'inativo', 'nome'], 'produtos_empresa_inativo_nome_idx');
        $this->addIndexIfPossible('pedidos', ['empresa_id', 'status', 'data_registro'], 'pedidos_empresa_status_data_idx');
        $this->addIndexIfPossible('vendas', ['empresa_id', 'data_registro'], 'vendas_empresa_data_idx');
        $this->addIndexIfPossible('venda_caixas', ['empresa_id', 'data_registro', 'estado_emissao'], 'venda_caixas_empresa_data_estado_idx');
        $this->addIndexIfPossible('venda_caixa_pre_vendas', ['empresa_id', 'created_at'], 'pre_vendas_empresa_created_idx');

        $this->addForeignIfPossible('rh_portal_funcionarios', 'perfil_id', 'rh_portal_perfis', 'id', 'rh_portal_funcionarios_perfil_id_fk', 'set null');
        $this->addForeignIfPossible('rh_portal_funcionarios', 'funcionario_id', 'funcionarios', 'id', 'rh_portal_funcionarios_funcionario_id_fk', 'cascade');
        $this->addForeignIfPossible('rh_portal_funcionarios', 'empresa_id', 'empresas', 'id', 'rh_portal_funcionarios_empresa_id_fk', 'cascade');
        $this->addForeignIfPossible('apuracao_mensals', 'funcionario_id', 'funcionarios', 'id', 'apuracao_mensals_funcionario_id_fk', 'cascade');
        $this->addForeignIfPossible('apuracao_mensals', 'empresa_id', 'empresas', 'id', 'apuracao_mensals_empresa_id_fk', 'cascade');
    }

    public function down(): void
    {
        $this->dropForeignIfExists('rh_portal_funcionarios', 'rh_portal_funcionarios_perfil_id_fk');
        $this->dropForeignIfExists('rh_portal_funcionarios', 'rh_portal_funcionarios_funcionario_id_fk');
        $this->dropForeignIfExists('rh_portal_funcionarios', 'rh_portal_funcionarios_empresa_id_fk');
        $this->dropForeignIfExists('apuracao_mensals', 'apuracao_mensals_funcionario_id_fk');
        $this->dropForeignIfExists('apuracao_mensals', 'apuracao_mensals_empresa_id_fk');

        $this->dropIndexIfExists('rh_portal_perfis', 'rh_portal_perfis_empresa_ativo_nome_idx');
        $this->dropIndexIfExists('rh_portal_funcionarios', 'rh_portal_funcionarios_empresa_func_ativo_idx');
        $this->dropIndexIfExists('apuracao_mensals', 'apuracao_mensals_empresa_func_periodo_idx');
        $this->dropIndexIfExists('produtos', 'produtos_empresa_inativo_nome_idx');
        $this->dropIndexIfExists('pedidos', 'pedidos_empresa_status_data_idx');
        $this->dropIndexIfExists('vendas', 'vendas_empresa_data_idx');
        $this->dropIndexIfExists('venda_caixas', 'venda_caixas_empresa_data_estado_idx');
        $this->dropIndexIfExists('venda_caixa_pre_vendas', 'pre_vendas_empresa_created_idx');
    }

    protected function addIndexIfPossible(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
            $tableBlueprint->index($columns, $indexName);
        });
    }

    protected function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
            $tableBlueprint->dropIndex($indexName);
        });
    }

    protected function addForeignIfPossible(string $table, string $column, string $referencesTable, string $referencesColumn, string $foreignName, string $onDelete = 'cascade'): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($referencesTable)) {
            return;
        }

        if (!Schema::hasColumn($table, $column) || !Schema::hasColumn($referencesTable, $referencesColumn)) {
            return;
        }

        if ($this->foreignExists($table, $foreignName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($column, $referencesTable, $referencesColumn, $foreignName, $onDelete) {
            $foreign = $tableBlueprint->foreign($column, $foreignName)->references($referencesColumn)->on($referencesTable);

            if ($onDelete === 'set null') {
                $foreign->nullOnDelete();
            } elseif ($onDelete === 'cascade') {
                $foreign->cascadeOnDelete();
            }
        });
    }

    protected function dropForeignIfExists(string $table, string $foreignName): void
    {
        if (!Schema::hasTable($table) || !$this->foreignExists($table, $foreignName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($foreignName) {
            $tableBlueprint->dropForeign($foreignName);
        });
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            foreach (DB::select("PRAGMA index_list('{$table}')") as $row) {
                if (($row->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        return !empty(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]));
    }

    protected function foreignExists(string $table, string $foreignName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return false;
        }

        $database = Schema::getConnection()->getDatabaseName();

        $rows = DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = ?",
            [$database, $table, $foreignName]
        );

        return !empty($rows);
    }
};
