<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class RefactorEnterpriseRouteAuditCommand extends Command
{
    protected $signature = 'refactor:enterprise-route-audit {--write : Persiste o relatório em docs/operacao}';

    protected $description = 'Audita as rotas enterprise para confirmar tenancy, camada web e hardening básico.';

    public function handle(): int
    {
        $targets = [
            'enterprise.ai.index',
            'enterprise.bi.index',
            'enterprise.financeiro.index',
            'enterprise.financeiro.operations',
            'enterprise.pdv.index',
            'enterprise.pdv.mobile',
            'enterprise.fiscal.snapshot',
            'enterprise.comercial.kpis',
            'enterprise.estoque.index',
            'enterprise.saas.index',
        ];

        $checks = collect($targets)->map(function (string $name) {
            $route = Route::getRoutes()->getByName($name);
            $middleware = $route ? $route->gatherMiddleware() : [];

            return [
                'route' => $name,
                'exists' => (bool) $route,
                'uri' => $route?->uri(),
                'tenant_context' => in_array('tenant.context', $middleware, true),
                'web' => in_array('web', $middleware, true),
                'enterprise_access' => in_array('enterpriseAccess', $middleware, true),
                'verifica_empresa' => in_array('verificaEmpresa', $middleware, true),
                'throttle_enterprise' => in_array('throttle:enterprise', $middleware, true),
                'middleware' => array_values($middleware),
            ];
        })->values()->all();

        $summary = [
            'generated_at' => now()->toIso8601String(),
            'router_aliases' => app('router')->getMiddleware(),
            'routes_checked' => count($checks),
            'checks' => $checks,
        ];

        foreach ($checks as $check) {
            $this->line(sprintf(
                '%s | existe=%s tenant=%s web=%s enterpriseAccess=%s verificaEmpresa=%s throttle=%s',
                $check['route'],
                $check['exists'] ? 'sim' : 'nao',
                $check['tenant_context'] ? 'sim' : 'nao',
                $check['web'] ? 'sim' : 'nao',
                $check['enterprise_access'] ? 'sim' : 'nao',
                $check['verifica_empresa'] ? 'sim' : 'nao',
                $check['throttle_enterprise'] ? 'sim' : 'nao'
            ));
        }

        if ($this->option('write')) {
            $dir = base_path('docs/operacao');
            File::ensureDirectoryExists($dir);

            $md = [
                '# Wave de Auditoria Enterprise (Tenancy + Hardening)',
                '',
                '- Gerado em: ' . $summary['generated_at'],
                '- Rotas auditadas: ' . $summary['routes_checked'],
                '',
                '| Rota | Existe | tenant.context | web | enterpriseAccess | verificaEmpresa | throttle:enterprise |',
                '| --- | --- | --- | --- | --- | --- | --- |',
            ];

            foreach ($checks as $check) {
                $md[] = sprintf(
                    '| %s | %s | %s | %s | %s | %s | %s |',
                    $check['route'],
                    $check['exists'] ? 'sim' : 'nao',
                    $check['tenant_context'] ? 'sim' : 'nao',
                    $check['web'] ? 'sim' : 'nao',
                    $check['enterprise_access'] ? 'sim' : 'nao',
                    $check['verifica_empresa'] ? 'sim' : 'nao',
                    $check['throttle_enterprise'] ? 'sim' : 'nao'
                );
            }

            $md[] = '';
            $md[] = '## Resultado';
            $md[] = '- A auditoria confirma se os módulos enterprise seguem o baseline de tenancy e proteção operacional.';
            $md[] = '- Este relatório não substitui homologação funcional completa por módulo.';

            File::put($dir . '/WAVE_ENTERPRISE_ROUTE_AUDIT.md', implode(PHP_EOL, $md) . PHP_EOL);
            File::put($dir . '/WAVE_ENTERPRISE_ROUTE_AUDIT.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return self::SUCCESS;
    }
}
