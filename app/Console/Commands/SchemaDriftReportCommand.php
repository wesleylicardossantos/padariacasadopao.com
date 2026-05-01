<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SchemaDriftReportCommand extends Command
{
    protected $signature = 'schema:drift-report {--write : Grava o relatório em storage/app/schema_drift_report.json e .md}';

    protected $description = 'Gera relatório operacional de drift entre código, migrations registradas e estrutura encontrada no banco.';

    public function handle(Filesystem $files): int
    {
        $migrationFiles = collect($files->files(database_path('migrations')))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => str_replace('.php', '', $file->getFilename()))
            ->sort()
            ->values()
            ->all();

        $error = null;

        try {
            $hasMigrationsTable = Schema::hasTable('migrations');
            $appliedMigrations = $hasMigrationsTable
                ? DB::table('migrations')->orderBy('batch')->pluck('migration')->all()
                : [];
            $tables = Schema::getTableListing();
        } catch (Throwable $e) {
            $hasMigrationsTable = false;
            $appliedMigrations = [];
            $tables = [];
            $error = $e->getMessage();
        }

        $criticalTables = (array) config('hardening.healthcheck.required_tables', []);
        $missingInDatabase = array_values(array_diff($migrationFiles, $appliedMigrations));
        $appliedButMissingInCode = array_values(array_diff($appliedMigrations, $migrationFiles));

        $tableDetails = [];
        foreach ($criticalTables as $table) {
            $tableDetails[] = $this->inspectTable($table);
        }

        $report = [
            'generated_at' => now()->toIso8601String(),
            'database' => config('database.default'),
            'database_available' => $error === null,
            'database_error' => $error,
            'migrations_table_exists' => $hasMigrationsTable,
            'migration_file_count' => count($migrationFiles),
            'applied_migration_count' => count($appliedMigrations),
            'table_count' => count($tables),
            'migration_files_not_applied' => $missingInDatabase,
            'applied_migrations_missing_in_code' => $appliedButMissingInCode,
            'critical_table_details' => $tableDetails,
            'tables' => $tables,
        ];

        $this->line('Migrations no código: ' . count($migrationFiles));
        $this->line('Migrations aplicadas: ' . count($appliedMigrations));
        $this->line('Tabelas encontradas: ' . count($tables));
        $this->line('Arquivos pendentes de aplicação: ' . count($missingInDatabase));
        $this->line('Migrations aplicadas sem arquivo no código: ' . count($appliedButMissingInCode));
        if ($error !== null) {
            $this->warn('Banco indisponível para inspeção online: ' . $error);
        }

        if ($this->option('write')) {
            $storageDir = storage_path('app');
            $docsDir = base_path('docs/operacao');
            $files->ensureDirectoryExists($storageDir);
            $files->ensureDirectoryExists($docsDir);
            $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $md = $this->toMarkdown($report);
            foreach ([
                $storageDir . '/schema_drift_report.json',
                $docsDir . '/schema_drift_report.json',
            ] as $path) {
                $files->put($path, $json);
            }
            foreach ([
                $storageDir . '/schema_drift_report.md',
                $docsDir . '/schema_drift_report.md',
            ] as $path) {
                $files->put($path, $md);
            }
            $this->info('Relatórios gravados em storage/app e docs/operacao');
        }

        return self::SUCCESS;
    }


    private function inspectTable(string $table): array
    {
        try {
            if (! Schema::hasTable($table)) {
                return [
                    'table' => $table,
                    'exists' => false,
                    'columns' => [],
                    'error' => null,
                ];
            }
        } catch (Throwable $e) {
            return [
                'table' => $table,
                'exists' => false,
                'columns' => [],
                'error' => $e->getMessage(),
            ];
        }

        if (! Schema::hasTable($table)) {
            return [
                'table' => $table,
                'exists' => false,
                'columns' => [],
                'error' => null,
            ];
        }

        try {
            return [
                'table' => $table,
                'exists' => true,
                'columns' => Schema::getColumnListing($table),
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'table' => $table,
                'exists' => true,
                'columns' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    private function toMarkdown(array $report): string
    {
        $md = "# Schema Drift Report\n\n";
        $md .= "Gerado em: {$report['generated_at']}\n\n";
        $md .= "- Banco padrão: {$report['database']}\n";
        $md .= "- Tabela migrations existe: " . ($report['migrations_table_exists'] ? 'sim' : 'não') . "\n";
        $md .= "- Migrations no código: {$report['migration_file_count']}\n";
        $md .= "- Migrations aplicadas: {$report['applied_migration_count']}\n";
        $md .= "- Tabelas encontradas: {$report['table_count']}\n\n";

        $md .= "## Migrations no código ainda não aplicadas\n";
        foreach ($report['migration_files_not_applied'] as $item) {
            $md .= "- {$item}\n";
        }
        if (empty($report['migration_files_not_applied'])) {
            $md .= "- Nenhuma\n";
        }

        $md .= "\n## Migrations aplicadas sem arquivo no código\n";
        foreach ($report['applied_migrations_missing_in_code'] as $item) {
            $md .= "- {$item}\n";
        }
        if (empty($report['applied_migrations_missing_in_code'])) {
            $md .= "- Nenhuma\n";
        }

        $md .= "\n## Tabelas críticas\n";
        foreach ($report['critical_table_details'] as $table) {
            $md .= "\n### {$table['table']}\n";
            $md .= '- Existe: ' . ($table['exists'] ? 'sim' : 'não') . "\n";
            if (! empty($table['columns'])) {
                foreach ($table['columns'] as $column) {
                    $md .= "- {$column}\n";
                }
            }
            if (! empty($table['error'])) {
                $md .= '- Erro: ' . $table['error'] . "\n";
            }
        }

        return $md;
    }
}
