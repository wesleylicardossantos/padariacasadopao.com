<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'apuracao_mensals' => 'id',
            'apuracao_salario_eventos' => 'id',
            'funcionario_eventos' => 'id',
            'evento_funcionarios' => 'id',
            'atividade_eventos' => 'id',
            'funcionario_os' => 'id',
            'contato_funcionarios' => 'id',
            'funcionarios_dependentes' => 'id',
            'funcionarios_ficha_admissao' => 'id',
            'rh_ferias' => 'id',
            'rh_faltas' => 'id',
            'rh_desligamentos' => 'id',
            'rh_movimentacoes' => 'id',
            'rh_ocorrencias' => 'id',
            'rh_documentos' => 'id',
        ];

        foreach ($tables as $table => $after) {
            $this->ensureEmpresaIdColumn($table, $after);
        }

        $this->backfillFromParent('apuracao_mensals', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('funcionario_eventos', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('evento_funcionarios', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('atividade_eventos', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('funcionario_os', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('contato_funcionarios', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('funcionarios_dependentes', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('funcionarios_ficha_admissao', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('rh_ferias', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('rh_faltas', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('rh_desligamentos', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('rh_movimentacoes', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('rh_ocorrencias', 'funcionarios', 'funcionario_id');
        $this->backfillFromParent('rh_documentos', 'funcionarios', 'funcionario_id');

        // Apuração salário eventos pode herdar da apuração mensal ou do evento salarial.
        $this->backfillFromParent('apuracao_salario_eventos', 'apuracao_mensals', 'apuracao_id');
        $this->backfillFromParent('apuracao_salario_eventos', 'evento_salarios', 'evento_id');

        // Fallbacks pelo evento quando existir essa relação.
        $this->backfillFromParent('evento_funcionarios', 'eventos', 'evento_id');
        $this->backfillFromParent('atividade_eventos', 'eventos', 'evento_id');

        $this->createCompositeIndexes();
    }

    public function down(): void
    {
        // Rollback destrutivo intencionalmente omitido para evitar perda de dados em produção.
    }

    private function ensureEmpresaIdColumn(string $table, string $after = 'id'): void
    {
        if (!Schema::hasTable($table) || Schema::hasColumn($table, 'empresa_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table, $after) {
            $column = $blueprint->unsignedInteger('empresa_id')->nullable();
            if (Schema::hasColumn($table, $after)) {
                $column->after($after);
            }
            $blueprint->index('empresa_id', $table . '_empresa_id_idx');
        });
    }

    private function backfillFromParent(string $table, string $parentTable, string $foreignKey, string $parentPrimaryKey = 'id'): void
    {
        if (!Schema::hasTable($table)
            || !Schema::hasTable($parentTable)
            || !Schema::hasColumn($table, 'empresa_id')
            || !Schema::hasColumn($table, $foreignKey)
            || !Schema::hasColumn($parentTable, $parentPrimaryKey)
            || !Schema::hasColumn($parentTable, 'empresa_id')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "UPDATE `{$table}` child\n"
                . "INNER JOIN `{$parentTable}` parent ON parent.`{$parentPrimaryKey}` = child.`{$foreignKey}`\n"
                . "SET child.`empresa_id` = parent.`empresa_id`\n"
                . "WHERE child.`empresa_id` IS NULL AND parent.`empresa_id` IS NOT NULL"
            );
            return;
        }

        // Fallback mais lento para outros drivers.
        DB::table($table)
            ->whereNull('empresa_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($table, $parentTable, $foreignKey, $parentPrimaryKey) {
                foreach ($rows as $row) {
                    $empresaId = DB::table($parentTable)
                        ->where($parentPrimaryKey, $row->{$foreignKey})
                        ->value('empresa_id');

                    if (!empty($empresaId)) {
                        DB::table($table)->where('id', $row->id)->update(['empresa_id' => $empresaId]);
                    }
                }
            });
    }

    private function createCompositeIndexes(): void
    {
        $indexes = [
            'apuracao_mensals' => ['empresa_id', 'funcionario_id'],
            'apuracao_salario_eventos' => ['empresa_id', 'apuracao_id'],
            'funcionario_eventos' => ['empresa_id', 'funcionario_id'],
            'evento_funcionarios' => ['empresa_id', 'funcionario_id'],
            'atividade_eventos' => ['empresa_id', 'funcionario_id'],
            'funcionario_os' => ['empresa_id', 'funcionario_id'],
            'contato_funcionarios' => ['empresa_id', 'funcionario_id'],
            'funcionarios_dependentes' => ['empresa_id', 'funcionario_id'],
            'funcionarios_ficha_admissao' => ['empresa_id', 'funcionario_id'],
            'rh_ferias' => ['empresa_id', 'funcionario_id'],
            'rh_faltas' => ['empresa_id', 'funcionario_id'],
            'rh_desligamentos' => ['empresa_id', 'funcionario_id'],
            'rh_movimentacoes' => ['empresa_id', 'funcionario_id'],
            'rh_ocorrencias' => ['empresa_id', 'funcionario_id'],
            'rh_documentos' => ['empresa_id', 'funcionario_id'],
        ];

        foreach ($indexes as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $missing = array_filter($columns, fn ($column) => !Schema::hasColumn($table, $column));
            if (!empty($missing)) {
                continue;
            }

            $indexName = $table . '_' . implode('_', $columns) . '_idx';
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                    $blueprint->index($columns, $indexName);
                });
            } catch (Throwable $e) {
                // índice já existe ou o banco recusou; ignoramos para manter a migration idempotente.
            }
        }
    }
};
