<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RefactorPhase4PremiumAuditCommand extends Command
{
    protected $signature = 'refactor:phase4-premium-audit {--write : Persist report under docs/refatoracao}';
    protected $description = 'Audita a Fase 4 premium: UX executiva, automacao e analytics SaaS.';

    public function handle(): int
    {
        $routes = collect(app('router')->getRoutes()->getRoutesByName());
        $checks = [
            'enterprise.saas.premium' => $routes->has('enterprise.saas.premium'),
            'enterprise.saas.executive' => $routes->has('enterprise.saas.executive'),
            'enterprise.saas.index' => $routes->has('enterprise.saas.index'),
        ];

        $tables = [];
        foreach (['saas_premium_notifications', 'saas_usage_snapshots', 'rh_alertas_inteligentes'] as $table) {
            try {
                $tables[$table] = Schema::hasTable($table);
            } catch (Throwable) {
                $tables[$table] = false;
            }
        }

        $views = [
            'resources/views/enterprise/saas/premium.blade.php' => File::exists(base_path('resources/views/enterprise/saas/premium.blade.php')),
            'resources/views/enterprise/saas/executive.blade.php' => File::exists(base_path('resources/views/enterprise/saas/executive.blade.php')),
        ];

        $report = [
            'generated_at' => now()->toIso8601String(),
            'routes' => $checks,
            'tables' => $tables,
            'views' => $views,
            'status' => collect($checks)->every(fn ($v) => $v) ? 'ok' : 'pending',
        ];

        foreach ($checks as $name => $ok) {
            $this->line($name . ': ' . ($ok ? 'ok' : 'missing'));
        }

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/refatoracao'));
            File::put(base_path('docs/refatoracao/FASE4_PREMIUM_AUDIT_2026-04-23.json'), json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            $md = "# Fase 4 Premium Audit

";
            foreach ($checks as $name => $ok) { $md .= "- {$name}: " . ($ok ? 'ok' : 'missing') . "
"; }
            foreach ($tables as $name => $ok) { $md .= "- tabela {$name}: " . ($ok ? 'ok' : 'pendente') . "
"; }
            foreach ($views as $name => $ok) { $md .= "- view {$name}: " . ($ok ? 'ok' : 'missing') . "
"; }
            File::put(base_path('docs/refatoracao/FASE4_PREMIUM_AUDIT_2026-04-23.md'), $md);
        }

        return self::SUCCESS;
    }
}
