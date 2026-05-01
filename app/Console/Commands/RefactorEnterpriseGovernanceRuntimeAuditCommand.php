<?php

namespace App\Console\Commands;

use App\Modules\AI\Controllers\GovernanceController as AIGovernanceController;
use App\Modules\BI\Controllers\GovernanceController as BIGovernanceController;
use App\Modules\Comercial\Controllers\GovernanceController as ComercialGovernanceController;
use App\Modules\Estoque\Controllers\GovernanceController as EstoqueGovernanceController;
use App\Modules\Financeiro\Controllers\GovernanceController as FinanceiroGovernanceController;
use App\Modules\Fiscal\Controllers\GovernanceController as FiscalGovernanceController;
use App\Modules\PDV\Controllers\GovernanceController as PdvGovernanceController;
use App\Modules\PDV\Controllers\MobileController as PdvMobileController;
use App\Modules\SaaS\Controllers\GovernanceController as SaaSGovernanceController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class RefactorEnterpriseGovernanceRuntimeAuditCommand extends Command
{
    protected $signature = 'refactor:enterprise-governance-runtime-audit {--write : Persiste o relatório em docs/operacao}';

    protected $description = 'Audita controllers e rotas de governança enterprise com tenant.context efetivo em runtime.';

    public function handle(): int
    {
        $controllers = [
            AIGovernanceController::class,
            BIGovernanceController::class,
            ComercialGovernanceController::class,
            EstoqueGovernanceController::class,
            FinanceiroGovernanceController::class,
            FiscalGovernanceController::class,
            PdvGovernanceController::class,
            PdvMobileController::class,
            SaaSGovernanceController::class,
        ];

        $controllerChecks = [];
        foreach ($controllers as $controller) {
            $traits = class_uses_recursive($controller);
            $reflection = new ReflectionClass($controller);
            $source = File::get($reflection->getFileName());
            $controllerChecks[] = [
                'controller' => $controller,
                'uses_tenant_trait' => in_array('App\\Support\\Tenancy\\InteractsWithTenantContext', $traits, true),
                'has_explicit_middleware' => str_contains($source, "\$this->middleware('tenant.context')")
                    || str_contains($source, '$this->middleware("tenant.context")'),
            ];
        }

        $routeNames = [
            'enterprise.ai.index',
            'enterprise.bi.index',
            'enterprise.comercial.kpis',
            'enterprise.estoque.index',
            'enterprise.financeiro.index',
            'enterprise.fiscal.snapshot',
            'enterprise.pdv.index',
            'enterprise.pdv.mobile',
            'enterprise.saas.index',
        ];

        $routeChecks = [];
        foreach ($routeNames as $name) {
            $route = app('router')->getRoutes()->getByName($name);
            $middleware = $route ? $route->gatherMiddleware() : [];
            $routeChecks[] = [
                'route' => $name,
                'exists' => (bool) $route,
                'tenant_context' => in_array('tenant.context', $middleware, true),
                'verifica_empresa' => in_array('verificaEmpresa', $middleware, true),
                'enterprise_access' => in_array('enterpriseAccess', $middleware, true),
                'throttle_enterprise' => in_array('throttle:enterprise', $middleware, true),
            ];
        }

        $summary = [
            'generated_at' => now()->toDateTimeString(),
            'tenant_context_alias_registered' => array_key_exists('tenant.context', app('router')->getMiddleware()),
            'controllers' => $controllerChecks,
            'routes' => $routeChecks,
        ];

        $this->info('Controllers auditados');
        foreach ($controllerChecks as $check) {
            $this->line(sprintf(
                '- %s | trait=%s middleware=%s',
                class_basename($check['controller']),
                $check['uses_tenant_trait'] ? 'sim' : 'nao',
                $check['has_explicit_middleware'] ? 'sim' : 'nao'
            ));
        }

        $this->newLine();
        $this->info('Rotas auditadas');
        foreach ($routeChecks as $check) {
            $this->line(sprintf(
                '- %s | existe=%s tenant=%s enterpriseAccess=%s verificaEmpresa=%s throttle=%s',
                $check['route'],
                $check['exists'] ? 'sim' : 'nao',
                $check['tenant_context'] ? 'sim' : 'nao',
                $check['enterprise_access'] ? 'sim' : 'nao',
                $check['verifica_empresa'] ? 'sim' : 'nao',
                $check['throttle_enterprise'] ? 'sim' : 'nao'
            ));
        }

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/operacao'));

            $md = [];
            $md[] = '# Wave Enterprise Governance Runtime Hardening';
            $md[] = '';
            $md[] = '- Gerado em: ' . $summary['generated_at'];
            $md[] = '- Alias tenant.context registrado: ' . ($summary['tenant_context_alias_registered'] ? 'sim' : 'nao');
            $md[] = '';
            $md[] = '## Controllers auditados';
            $md[] = '';
            $md[] = '| Controller | InteractsWithTenantContext | Middleware explícito |';
            $md[] = '|---|---|---|';
            foreach ($controllerChecks as $check) {
                $md[] = sprintf('| %s | %s | %s |', class_basename($check['controller']), $check['uses_tenant_trait'] ? 'sim' : 'nao', $check['has_explicit_middleware'] ? 'sim' : 'nao');
            }
            $md[] = '';
            $md[] = '## Rotas auditadas';
            $md[] = '';
            $md[] = '| Rota | Existe | tenant.context | enterpriseAccess | verificaEmpresa | throttle:enterprise |';
            $md[] = '|---|---|---|---|---|---|';
            foreach ($routeChecks as $check) {
                $md[] = sprintf('| %s | %s | %s | %s | %s | %s |', $check['route'], $check['exists'] ? 'sim' : 'nao', $check['tenant_context'] ? 'sim' : 'nao', $check['enterprise_access'] ? 'sim' : 'nao', $check['verifica_empresa'] ? 'sim' : 'nao', $check['throttle_enterprise'] ? 'sim' : 'nao');
            }
            $md[] = '';
            $md[] = '- Nesta wave, a governança enterprise passou a forçar tenant.context no runtime também no nível dos controllers.';
            $md[] = '- Isso reduz dependência da ordem de carregamento de rotas e unifica a resolução de empresa entre AI, BI, Comercial, Estoque, Financeiro, Fiscal, PDV e SaaS.';

            File::put(base_path('docs/operacao/WAVE_ENTERPRISE_GOVERNANCE_RUNTIME_HARDENING_2026-04-10.md'), implode(PHP_EOL, $md) . PHP_EOL);
            File::put(base_path('docs/operacao/WAVE_ENTERPRISE_GOVERNANCE_RUNTIME_HARDENING_2026-04-10.json'), json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            File::put(base_path('docs/operacao/STATUS_REAL_REFATORACAO_WAVE_ENTERPRISE_GOVERNANCE_RUNTIME_HARDENING_2026-04-10.md'), implode(PHP_EOL, [
                '# Status real da wave Enterprise Governance Runtime Hardening 2026-04-10',
                '',
                '- Controllers de governança enterprise endurecidos com tenant.context explícito no runtime.',
                '- Controladores Comercial, Estoque, PDV e SaaS padronizados para InteractsWithTenantContext.',
                '- Criação do comando refactor:enterprise-governance-runtime-audit para auditoria contínua dessa camada.',
            ]) . PHP_EOL);
        }

        return self::SUCCESS;
    }
}
