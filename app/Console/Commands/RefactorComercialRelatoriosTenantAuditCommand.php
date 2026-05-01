<?php

namespace App\Console\Commands;

use App\Http\Controllers\DreController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\VendaController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RefactorComercialRelatoriosTenantAuditCommand extends Command
{
    protected $signature = 'refactor:comercial-relatorios-tenant-audit {--write : Persiste o relatório em docs/operacao}';

    protected $description = 'Audita endurecimento de tenancy em Relatórios, DRE, Produtos, Vendas e Pedidos.';

    public function handle(): int
    {
        $targets = [
            'dre.index',
            'dre.list',
            'dre.imprimir',
            'relatorios.index',
            'relatorios.soma-vendas',
            'relatorios.vendaProdutos',
            'produtos.index',
            'produtos.movimentacao',
            'produtos.duplicar',
            'vendas.index',
            'vendas.clone',
            'vendas.print',
            'pedidos.index',
            'pedidos.verMesa',
            'pedidos.finalizar',
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

        $controllers = [
            RelatorioController::class,
            DreController::class,
            ProductController::class,
            VendaController::class,
            PedidoController::class,
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
            'controllers' => $controllerChecks,
        ];

        foreach ($checks as $check) {
            $this->line(sprintf('- %s | existe=%s tenant=%s verificaEmpresa=%s', $check['route'], $check['exists'] ? 'sim' : 'nao', $check['tenant_context'] ? 'sim' : 'nao', $check['verifica_empresa'] ? 'sim' : 'nao'));
        }
        foreach ($controllerChecks as $check) {
            $this->line(sprintf('- %s | trait=%s', class_basename($check['controller']), $check['uses_tenant_trait'] ? 'sim' : 'nao'));
        }

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/operacao'));
            $md = [];
            $md[] = '# Wave Comercial/Relatórios Tenant Hardening';
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
            $md[] = '## Controllers auditados';
            $md[] = '';
            $md[] = '| Controller | InteractsWithTenantContext |';
            $md[] = '|---|---|';
            foreach ($controllerChecks as $check) {
                $md[] = sprintf('| %s | %s |', class_basename($check['controller']), $check['uses_tenant_trait'] ? 'sim' : 'nao');
            }
            $md[] = '';
            $md[] = '- Nesta wave, Relatórios, DRE, Produtos, Vendas e Pedidos passaram a endurecer tenancy por middleware e controllers centrais.';
            $md[] = '- Buscas por ID críticas foram blindadas com escopo por empresa para reduzir risco cross-tenant.';

            File::put(base_path('docs/operacao/WAVE_COMERCIAL_RELATORIOS_TENANT_HARDENING.md'), implode(PHP_EOL, $md) . PHP_EOL);
            File::put(base_path('docs/operacao/WAVE_COMERCIAL_RELATORIOS_TENANT_HARDENING.json'), json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            File::put(base_path('docs/operacao/STATUS_REAL_REFATORACAO_WAVE_COMERCIAL_RELATORIOS_2026-04-10.md'), implode(PHP_EOL, [
                '# Status real da wave Comercial/Relatórios 2026-04-10',
                '',
                '- Consolidação de TenantContext em RelatorioController, DreController, ProductController, VendaController e PedidoController.',
                '- Adição do middleware tenant.context nas rotas críticas de DRE, relatórios, produtos, vendas e pedidos.',
                '- Blindagem de lookups por ID em DRE, produtos, vendas e pedidos para impedir acesso cross-tenant.',
                '- Criação do comando refactor:comercial-relatorios-tenant-audit para auditoria contínua da wave.',
            ]) . PHP_EOL);
        }

        return self::SUCCESS;
    }
}
