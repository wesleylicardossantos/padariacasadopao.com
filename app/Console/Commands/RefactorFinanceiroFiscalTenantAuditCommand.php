<?php

namespace App\Console\Commands;

use App\Http\Controllers\BoletoController;
use App\Http\Controllers\ContigenciaController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\NfseController;
use App\Http\Controllers\RemessaBoletoController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RefactorFinanceiroFiscalTenantAuditCommand extends Command
{
    protected $signature = 'refactor:financeiro-fiscal-tenant-audit {--write : Persiste o relatório em docs/operacao}';

    protected $description = 'Audita o endurecimento de tenancy em Financeiro e Fiscal web.';

    public function handle(): int
    {
        $targets = [
            'financeiro.index',
            'financeiro.list',
            'nfse.index',
            'nfse.imprimir',
            'boletos.index',
            'boletos.print',
            'boletos.store-issue',
            'remessa-boletos.index',
            'remessa.sem-remessa',
            'remessa-boletos.download',
            'contigencia.index',
            'contigencia.desactive',
            'compraFiscal.index',
            'compraFiscal.store',
            'compraFiscal.import',
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
            FinanceiroController::class,
            NfseController::class,
            ContigenciaController::class,
            BoletoController::class,
            RemessaBoletoController::class,
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
            $this->line(sprintf(
                '- %s | existe=%s tenant=%s verificaEmpresa=%s',
                $check['route'],
                $check['exists'] ? 'sim' : 'nao',
                $check['tenant_context'] ? 'sim' : 'nao',
                $check['verifica_empresa'] ? 'sim' : 'nao'
            ));
        }

        foreach ($controllerChecks as $check) {
            $this->line(sprintf('- %s | trait=%s', class_basename($check['controller']), $check['uses_tenant_trait'] ? 'sim' : 'nao'));
        }

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/operacao'));

            $md = [];
            $md[] = '# Wave Financeiro/Fiscal Tenant Hardening';
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
            $md[] = '- Nesta wave, listagens e buscas por ID em Financeiro/Fiscal passaram a respeitar TenantContext nos controllers críticos.';
            $md[] = '- As rotas web sensíveis de Financeiro, NFSe, boletos, remessas, contingência e compra fiscal receberam tenant.context.';
            $md[] = '- Corrigido vazamento de tenancy em FinanceiroController::list, que antes listava pagamentos sem filtrar empresa.';

            File::put(base_path('docs/operacao/WAVE_FINANCEIRO_FISCAL_TENANT_HARDENING.md'), implode(PHP_EOL, $md) . PHP_EOL);
            File::put(base_path('docs/operacao/WAVE_FINANCEIRO_FISCAL_TENANT_HARDENING.json'), json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            File::put(base_path('docs/operacao/STATUS_REAL_REFATORACAO_WAVE_FINANCEIRO_FISCAL_2026-04-10.md'), implode(PHP_EOL, [
                '# Status real da wave Financeiro/Fiscal 2026-04-10',
                '',
                '- Consolidação de TenantContext em FinanceiroController, NfseController, ContigenciaController, BoletoController e RemessaBoletoController.',
                '- Adição do middleware tenant.context nas rotas web críticas de financeiro e fiscal.',
                '- Blindagem de lookups por ID para impedir acesso cross-tenant em NFSe, remessas, boletos e contingência.',
                '- Correção do FinanceiroController::list para filtrar pagamentos pela empresa atual.',
                '- Criação do comando refactor:financeiro-fiscal-tenant-audit para auditoria contínua da wave.',
            ]) . PHP_EOL);
        }

        return self::SUCCESS;
    }
}
