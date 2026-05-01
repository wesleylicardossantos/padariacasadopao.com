<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemHealthCheckCommand extends Command
{
    protected $signature = 'system:healthcheck {--write : Grava relatório em storage/app/healthcheck.json e .md}';
    protected $description = 'Valida banco, tabelas críticas, configuração mínima, permissões e artefatos operacionais.';

    public function handle(Filesystem $files): int
    {
        $requiredTables = (array) config('hardening.healthcheck.required_tables', []);
        $missingTables = [];
        $databaseError = null;
        $databaseAvailable = false;
        $migrationsTableExists = false;

        try {
            DB::select('select 1');
            $databaseAvailable = true;
            $migrationsTableExists = Schema::hasTable('migrations');
        } catch (Throwable $e) {
            $databaseError = $e->getMessage();
        }

        if ($databaseAvailable) {
            foreach ($requiredTables as $table) {
                if (! Schema::hasTable($table)) {
                    $missingTables[] = $table;
                }
            }
        }

        $report = [
            'timestamp' => now()->toIso8601String(),
            'app_env' => config('app.env'),
            'app_debug' => (bool) config('app.debug'),
            'app_key_present' => ! empty(config('app.key')),
            'queue_connection' => (string) config('queue.default'),
            'database' => $databaseAvailable,
            'database_error' => $databaseError,
            'storage_writable' => is_writable(storage_path()),
            'bootstrap_cache_writable' => is_writable(base_path('bootstrap/cache')),
            'required_tables' => $requiredTables,
            'missing_tables' => $missingTables,
            'migrations_table_exists' => $migrationsTableExists,
            'storage_reports' => [
                'schema_drift_report' => File::exists(base_path('docs/operacao/schema_drift_report.json')) || File::exists(storage_path('app/schema_drift_report.json')),
                'healthcheck_report' => File::exists(base_path('docs/operacao/system_healthcheck.json')) || File::exists(storage_path('app/healthcheck.json')),
                'stock_reconcile_report' => ! empty(File::glob(storage_path('app/stock_reconcile_empresa_*.json'))),
            ],
        ];

        try {
            $scheduleExitCode = Artisan::call('schedule:list');
            $report['schedule_list_ok'] = $scheduleExitCode === self::SUCCESS;
            $report['schedule_list_error'] = null;
        } catch (Throwable $e) {
            $report['schedule_list_ok'] = false;
            $report['schedule_list_error'] = $e->getMessage();
        }

        $ok = $report['app_key_present']
            && $report['storage_writable']
            && $report['bootstrap_cache_writable']
            && empty($missingTables)
            && $report['schedule_list_ok'];

        $this->line('database: ' . ($report['database'] ? 'OK' : 'INDISPONÍVEL'));
        if ($databaseError) {
            $this->warn('database error: ' . $databaseError);
        }
        $this->line('app key: ' . ($report['app_key_present'] ? 'OK' : 'FALHA'));
        $this->line('app debug: ' . ($report['app_debug'] ? 'ligado' : 'desligado'));
        $this->line('queue: ' . $report['queue_connection']);
        $this->line('storage writable: ' . ($report['storage_writable'] ? 'OK' : 'FALHA'));
        $this->line('bootstrap/cache writable: ' . ($report['bootstrap_cache_writable'] ? 'OK' : 'FALHA'));
        $this->line('missing tables: ' . (empty($missingTables) ? 'nenhuma/indisponível' : implode(', ', $missingTables)));
        $this->line('schedule:list: ' . ($report['schedule_list_ok'] ? 'OK' : 'FALHA'));
        if (! empty($report['schedule_list_error'])) {
            $this->warn('schedule:list error: ' . $report['schedule_list_error']);
        }

        if ($this->option('write')) {
            $storageDir = storage_path('app');
            $docsDir = base_path('docs/operacao');
            $files->ensureDirectoryExists($storageDir);
            $files->ensureDirectoryExists($docsDir);
            $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $md = $this->toMarkdown($report);
            $files->put($storageDir . '/healthcheck.json', $json);
            $files->put($storageDir . '/healthcheck.md', $md);
            $files->put($docsDir . '/system_healthcheck.json', $json);
            $files->put($docsDir . '/system_healthcheck.md', $md);
            $this->info('Relatórios gravados em storage/app e docs/operacao');
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }


    private function toMarkdown(array $report): string
    {
        $md = "# System Healthcheck\n\n";
        foreach ($report as $key => $value) {
            if (is_array($value)) {
                $md .= "## {$key}\n";
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        $md .= "### {$subKey}\n";
                        foreach ($subValue as $item) {
                            $md .= "- {$item}\n";
                        }
                    } else {
                        $render = is_bool($subValue) ? ($subValue ? 'true' : 'false') : $subValue;
                        $md .= "- {$subKey}: {$render}\n";
                    }
                }
                $md .= "\n";
                continue;
            }

            $render = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $md .= "- {$key}: {$render}\n";
        }

        return $md;
    }
}
