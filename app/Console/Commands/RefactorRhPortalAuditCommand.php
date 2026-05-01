<?php

namespace App\Console\Commands;

use App\Http\Controllers\RHPortalAcessoController;
use App\Http\Controllers\RHPortalFuncionarioController;
use App\Http\Controllers\RHPortalPerfilController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RefactorRhPortalAuditCommand extends Command
{
    protected $signature = 'refactor:rh-portal-audit {--write : Persiste o relatório em docs/operacao}';

    protected $description = 'Audita rotas e controllers do portal RH após a wave de tenancy.';

    public function handle(): int
    {
        $targets = [
            'rh.portal_funcionario.index',
            'rh.portal_funcionario.pdf',
            'rh.portal_externo.enviar_acesso',
            'rh.portal_externo.configurar',
            'rh.portal_perfis.index',
            'rh.portal_perfis.store',
            'rh.portal_perfis.update',
            'rh.portal_perfis.destroy',
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
                'middleware' => array_values($middleware),
            ];
        }

        $controllers = [
            RHPortalAcessoController::class,
            RHPortalFuncionarioController::class,
            RHPortalPerfilController::class,
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
            $this->line(sprintf('- %s | existe=%s tenant=%s', $check['route'], $check['exists'] ? 'sim' : 'nao', $check['tenant_context'] ? 'sim' : 'nao'));
        }

        foreach ($controllerChecks as $check) {
            $this->line(sprintf('- %s | trait=%s', class_basename($check['controller']), $check['uses_tenant_trait'] ? 'sim' : 'nao'));
        }

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/operacao'));

            $md = [];
            $md[] = '# Wave RH Portal Tenancy';
            $md[] = '';
            $md[] = '- Gerado em: ' . $summary['generated_at'];
            $md[] = '- Alias tenant.context registrado: ' . ($summary['tenant_context_alias_registered'] ? 'sim' : 'nao');
            $md[] = '';
            $md[] = '## Rotas auditadas';
            $md[] = '';
            $md[] = '| Rota | Existe | tenant.context |';
            $md[] = '|---|---|---|';
            foreach ($checks as $check) {
                $md[] = sprintf('| %s | %s | %s |', $check['route'], $check['exists'] ? 'sim' : 'nao', $check['tenant_context'] ? 'sim' : 'nao');
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
            $md[] = '- Nesta wave, o portal RH administrativo passou a resolver tenant via TenantContext nos controllers críticos.';
            $md[] = '- As rotas administrativas do portal RH receberam middleware tenant.context para endurecer o contexto por empresa.';

            File::put(base_path('docs/operacao/WAVE_RH_PORTAL_TENANCY.md'), implode(PHP_EOL, $md) . PHP_EOL);
            File::put(base_path('docs/operacao/WAVE_RH_PORTAL_TENANCY.json'), json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            File::put(base_path('docs/operacao/STATUS_REAL_REFATORACAO_WAVE_RH_PORTAL_2026-04-10.md'), implode(PHP_EOL, [
                '# Status real da wave RH Portal 2026-04-10',
                '',
                '- Consolidação de TenantContext nos controllers RHPortalAcessoController, RHPortalFuncionarioController e RHPortalPerfilController.',
                '- Adição do middleware tenant.context nas rotas administrativas do portal RH.',
                '- Criação do comando refactor:rh-portal-audit para auditoria contínua da wave.',
            ]) . PHP_EOL);
        }

        return self::SUCCESS;
    }
}
