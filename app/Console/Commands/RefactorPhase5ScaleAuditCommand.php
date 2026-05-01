<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RefactorPhase5ScaleAuditCommand extends Command
{
    protected $signature = 'refactor:phase5-scale-audit {--write : Persiste o relatório em docs/refatoracao}';
    protected $description = 'Audita os entregáveis da Fase 5: cache, jobs, API interna, observabilidade e tenancy hardening.';

    public function handle(): int
    {
        $routes = collect(app('router')->getRoutes()->getRoutesByName());
        try {
            $jobsTable = Schema::hasTable('jobs');
            $cacheTable = Schema::hasTable('cache');
            $failedJobsTable = Schema::hasTable('failed_jobs');
            $dbError = null;
        } catch (Throwable $e) {
            $jobsTable = false;
            $cacheTable = false;
            $failedJobsTable = false;
            $dbError = $e->getMessage();
        }

        $checks = [
            'enterprise.saas.scale' => (bool) $routes->get('enterprise.saas.scale'),
            'enterprise.saas.observability' => (bool) $routes->get('enterprise.saas.observability'),
            'api.internal.saas.executive' => (bool) $routes->get('api.internal.saas.executive'),
            'api.internal.saas.premium' => (bool) $routes->get('api.internal.saas.premium'),
            'api.internal.saas.scale' => (bool) $routes->get('api.internal.saas.scale'),
            'jobs_table' => $jobsTable,
            'cache_table' => $cacheTable,
            'failed_jobs_table' => $failedJobsTable,
            'database_error' => $dbError,
        ];

        foreach ($checks as $key => $value) {
            $this->line(sprintf('- %s: %s', $key, $value ? 'sim' : 'nao'));
        }

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/refatoracao'));
            $md = [
                '# FASE 5 SCALE AUDIT',
                '',
            ];
            foreach ($checks as $key => $value) {
                $md[] = '- ' . $key . ': ' . ($value ? 'sim' : 'nao');
            }
            File::put(base_path('docs/refatoracao/FASE5_SCALE_AUDIT_2026-04-23.md'), implode(PHP_EOL, $md) . PHP_EOL);
            File::put(base_path('docs/refatoracao/FASE5_SCALE_AUDIT_2026-04-23.json'), json_encode($checks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return self::SUCCESS;
    }
}
