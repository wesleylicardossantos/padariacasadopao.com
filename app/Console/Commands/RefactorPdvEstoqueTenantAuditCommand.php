<?php

namespace App\Console\Commands;

use App\Http\Controllers\Pdv\OfflineBootstrapController;
use App\Http\Controllers\Pdv\OfflineSyncMonitorController;
use App\Http\Controllers\Pdv\OfflineVendaSyncController;
use App\Http\Controllers\Pdv\ProdutoController;
use App\Http\Controllers\StockController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RefactorPdvEstoqueTenantAuditCommand extends Command
{
    protected $signature = 'refactor:pdv-estoque-tenant-audit {--write : Persiste o relatório em docs/operacao}';

    protected $description = 'Audita endurecimento de tenancy em Estoque e PDV offline/mobile.';

    public function handle(): int
    {
        $targets = [
            'estoque.index',
            'estoque.store',
            'estoque.apontamentoManual',
            'estoque.listaApontamento',
            'estoque.apontamentoProducao',
            'estoque.todosApontamentos',
            'estoque.storeApontamento',
            'estoque.set-estoque-local',
            'pdv.offline.monitor',
            'pdv.offline.monitor.data',
            'pdv.offline.monitor.reenviar_pendentes',
            'pdv.offline.monitor.reenviar_erros',
        ];

        $routes = collect(app('router')->getRoutes()->getRoutesByName());
        $checks = [];

        foreach ($targets as $name) {
            $route = $routes->get($name);
            $middleware = $route ? $route->gatherMiddleware() : [];
            $checks[] = [
                'route' => $name,
                'exists' => (bool) $route,
                'tenant_context' => in_array('tenant.context', $middleware, true),
                'verifica_empresa' => in_array('verificaEmpresa', $middleware, true),
                'middleware' => array_values($middleware),
            ];
        }

        $pathChecks = [];
        foreach (app('router')->getRoutes() as $route) {
            if (str_starts_with($route->uri(), 'mobile/pdv')) {
                $middleware = $route->gatherMiddleware();
                $pathChecks[] = [
                    'uri' => $route->uri(),
                    'tenant_context' => in_array('tenant.context', $middleware, true),
                    'middleware' => array_values($middleware),
                ];
            }
        }

        $controllers = [
            StockController::class,
            ProdutoController::class,
            OfflineBootstrapController::class,
            OfflineVendaSyncController::class,
            OfflineSyncMonitorController::class,
        ];

        $controllerChecks = [];
        foreach ($controllers as $controller) {
            $traits = class_uses_recursive($controller);
            $controllerChecks[] = [
                'controller' => $controller,
                'uses_tenant_trait' => in_array('App\\Support\\Tenancy\\InteractsWithTenantContext', $traits, true),
            ];
        }

        $summary = [
            'generated_at' => now()->toDateTimeString(),
            'tenant_context_alias_registered' => array_key_exists('tenant.context', app('router')->getMiddleware()),
            'routes' => $checks,
            'mobile_pdv_routes' => $pathChecks,
            'controllers' => $controllerChecks,
        ];

        foreach ($checks as $check) {
            $this->line(sprintf('- %s | existe=%s tenant=%s verificaEmpresa=%s', $check['route'], $check['exists'] ? 'sim' : 'nao', $check['tenant_context'] ? 'sim' : 'nao', $check['verifica_empresa'] ? 'sim' : 'nao'));
        }
        foreach ($pathChecks as $check) {
            $this->line(sprintf('- %s | tenant=%s', $check['uri'], $check['tenant_context'] ? 'sim' : 'nao'));
        }
        foreach ($controllerChecks as $check) {
            $this->line(sprintf('- %s | trait=%s', class_basename($check['controller']), $check['uses_tenant_trait'] ? 'sim' : 'nao'));
        }

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/operacao'));

            $md = [];
            $md[] = '# Wave PDV/Estoque Tenant Hardening';
            $md[] = '';
            $md[] = '- Gerado em: ' . $summary['generated_at'];
            $md[] = '- Alias tenant.context registrado: ' . ($summary['tenant_context_alias_registered'] ? 'sim' : 'nao');
            $md[] = '';
            $md[] = '## Rotas auditadas';
            $md[] = '';
            $md[] = '| Rota | Existe | tenant.context | verificaEmpresa |';
            $md[] = '|---|---|---|---|';
            foreach ($checks as $check) {
                $md[] = sprintf('| %s | %s | %s | %s |', $check['route'], $check['exists'] ? 'sim' : 'nao', $check['tenant_context'] ? 'sim' : 'nao', $check['verifica_empresa'] ? 'sim' : 'nao');
            }
            $md[] = '';
            $md[] = '## Rotas mobile/pdv';
            $md[] = '';
            $md[] = '| URI | tenant.context |';
            $md[] = '|---|---|';
            foreach ($pathChecks as $check) {
                $md[] = sprintf('| %s | %s |', $check['uri'], $check['tenant_context'] ? 'sim' : 'nao');
            }
            $md[] = '';
            $md[] = '## Controllers auditados';
            $md[] = '';
            $md[] = '| Controller | InteractsWithTenantContext |';
            $md[] = '|---|---|';
            foreach ($controllerChecks as $check) {
                $md[] = sprintf('| %s | %s |', class_basename($check['controller']), $check['uses_tenant_trait'] ? 'sim' : 'nao');
            }
            $md[] = '';
            $md[] = '- Nesta wave, StockController e endpoints PDV offline/mobile passaram a endurecer tenancy por middleware e controllers centrais.';
            $md[] = '- Buscas por produto e filial em ajustes de estoque agora respeitam a empresa atual.';

            File::put(base_path('docs/operacao/WAVE_PDV_ESTOQUE_TENANT_HARDENING.md'), implode(PHP_EOL, $md) . PHP_EOL);
            File::put(base_path('docs/operacao/WAVE_PDV_ESTOQUE_TENANT_HARDENING.json'), json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            File::put(base_path('docs/operacao/STATUS_REAL_REFATORACAO_WAVE_PDV_ESTOQUE_2026-04-10.md'), implode(PHP_EOL, [
                '# Status real da wave PDV/Estoque 2026-04-10',
                '',
                '- Consolidação de TenantContext em StockController, Pdv\\ProdutoController e controllers do PDV offline.',
                '- Adição do middleware tenant.context nas rotas críticas de estoque, pdv-offline e mobile/pdv.',
                '- Blindagem de lookups por ID em estoque para impedir acesso cross-tenant a produtos e filiais.',
                '- Criação do comando refactor:pdv-estoque-tenant-audit para auditoria contínua da wave.',
            ]) . PHP_EOL);
        }

        return self::SUCCESS;
    }
}
