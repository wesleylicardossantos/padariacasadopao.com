<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RefactorPhase3FinalAuditCommand extends Command
{
    protected $signature = 'refactor:phase3-final-audit {--write : Grava o relatório em docs/refatoracao}';
    protected $description = 'Audita o fechamento de todas as waves da Fase 3 SaaS Core';

    public function handle(): int
    {
        $routes = collect(app('router')->getRoutes()->getRoutesByName());
        $checks = [
            'enterprise.saas.executive',
            'rh.acl.index',
            'rh.acl.assign',
            'rh.portal_externo.dashboard',
            'rh.portal_externo.dossie',
            'rh.portal_externo.documentos_rescisao',
        ];

        $report = [
            'generated_at' => now()->toIso8601String(),
            'routes' => collect($checks)->map(fn ($name) => ['route' => $name, 'exists' => (bool) $routes->get($name)])->all(),
            'tables' => $this->safeTables([
                'rh_acl_papeis',
                'rh_portal_perfis',
                'rh_admin_action_audits',
            ]),
            'views' => [
                'enterprise/saas/executive' => File::exists(resource_path('views/enterprise/saas/executive.blade.php')),
                'rh/acl/index' => File::exists(resource_path('views/rh/acl/index.blade.php')),
                'rh/portal_funcionario/dossie_externo' => File::exists(resource_path('views/rh/portal_funcionario/dossie_externo.blade.php')),
            ],
        ];

        foreach ($report['routes'] as $route) {
            $this->line($route['route'] . ': ' . ($route['exists'] ? 'sim' : 'nao'));
        }

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/refatoracao'));
            File::put(base_path('docs/refatoracao/FASE3_FINAL_AUDIT_2026-04-23.json'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            File::put(base_path('docs/refatoracao/FASE3_FINAL_AUDIT_2026-04-23.md'), $this->toMarkdown($report));
        }

        return self::SUCCESS;
    }

    private function safeTables(array $tables): array
    {
        $result = [];
        foreach ($tables as $table) {
            try {
                $result[$table] = Schema::hasTable($table);
            } catch (Throwable) {
                $result[$table] = false;
            }
        }
        return $result;
    }

    private function toMarkdown(array $report): string
    {
        $lines = ['# Fase 3 Final Audit', '', '- Gerado em: ' . $report['generated_at'], '', '## Rotas'];
        foreach ($report['routes'] as $route) {
            $lines[] = '- ' . $route['route'] . ': ' . ($route['exists'] ? 'sim' : 'nao');
        }
        $lines[] = '';
        $lines[] = '## Tabelas';
        foreach ($report['tables'] as $table => $ok) {
            $lines[] = '- ' . $table . ': ' . ($ok ? 'sim' : 'nao');
        }
        $lines[] = '';
        $lines[] = '## Views';
        foreach ($report['views'] as $view => $ok) {
            $lines[] = '- ' . $view . ': ' . ($ok ? 'sim' : 'nao');
        }
        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
