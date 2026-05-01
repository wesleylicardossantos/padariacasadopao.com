<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class RefactorWaveRhTenancyReportCommand extends Command
{
    protected $signature = 'refactor:wave-rh-tenancy-report {--write : Persiste o relatório em docs/operacao}';
    protected $description = 'Gera um relatório da wave executiva de tenancy + RBAC aplicada ao módulo RH.';

    public function handle(): int
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->uri(), 'rh'))
            ->values();

        $named = [
            'rh.dashboard_executivo.index',
            'rh.painel_dono.index',
            'rh.dossie.show',
            'rh.dossie.documentos.store',
            'rh.dossie.documentos.destroy',
            'rh.dossie.eventos.store',
            'rh.dossie.eventos.destroy',
        ];

        $routeChecks = collect($named)->map(function (string $name) {
            $route = Route::getRoutes()->getByName($name);
            $middleware = $route ? $route->gatherMiddleware() : [];

            return [
                'route' => $name,
                'exists' => (bool) $route,
                'tenant_context' => in_array('tenant.context', $middleware, true),
                'rh_permission' => collect($middleware)->contains(fn ($item) => str_starts_with((string) $item, 'rh.permission:')),
                'middleware' => array_values($middleware),
            ];
        })->all();

        $summary = [
            'generated_at' => now()->toDateTimeString(),
            'module' => 'RH',
            'rh_route_count' => $routes->count(),
            'tenant_context_alias_registered' => array_key_exists('tenant.context', app('router')->getMiddleware()),
            'rh_permission_alias_registered' => array_key_exists('rh.permission', app('router')->getMiddleware()),
            'checked_routes' => $routeChecks,
        ];

        $this->info('Wave RH/Tenancy report');
        $this->line('Rotas RH encontradas: ' . $summary['rh_route_count']);
        foreach ($routeChecks as $check) {
            $this->line(sprintf(
                '- %s | existe=%s tenant=%s rh_permission=%s',
                $check['route'],
                $check['exists'] ? 'sim' : 'nao',
                $check['tenant_context'] ? 'sim' : 'nao',
                $check['rh_permission'] ? 'sim' : 'nao'
            ));
        }

        if ($this->option('write')) {
            $dir = base_path('docs/operacao');
            File::ensureDirectoryExists($dir);

            $md = [];
            $md[] = '# Wave executiva RH + Tenancy + RBAC';
            $md[] = '';
            $md[] = '- Gerado em: ' . $summary['generated_at'];
            $md[] = '- Rotas RH encontradas: ' . $summary['rh_route_count'];
            $md[] = '- Alias tenant.context registrado: ' . ($summary['tenant_context_alias_registered'] ? 'sim' : 'nao');
            $md[] = '- Alias rh.permission registrado: ' . ($summary['rh_permission_alias_registered'] ? 'sim' : 'nao');
            $md[] = '';
            $md[] = '## Rotas auditadas';
            $md[] = '';
            $md[] = '| Rota | Existe | tenant.context | rh.permission |';
            $md[] = '|---|---|---|---|';
            foreach ($routeChecks as $check) {
                $md[] = sprintf('| %s | %s | %s | %s |', $check['route'], $check['exists'] ? 'sim' : 'nao', $check['tenant_context'] ? 'sim' : 'nao', $check['rh_permission'] ? 'sim' : 'nao');
            }
            $md[] = '';
            $md[] = '## Objetivo';
            $md[] = 'Aplicar uma wave de baixo risco que fortalece isolamento por empresa e explicita permissões em pontos críticos do módulo RH sem reescrever o fluxo legado.';
            $md[] = '';
            $md[] = '## Observações';
            $md[] = '- O middleware tenant.context foi consolidado no grupo /rh modular.';
            $md[] = '- As rotas sensíveis de dossiê, documentos e fechamento/reabertura de folha receberam permissão explícita.';
            $md[] = '- A política atual permanece fail-open quando a infraestrutura ACL ainda não está pronta, reduzindo risco operacional durante migração gradual.';

            File::put($dir . '/WAVE_EXECUTIVA_RH_TENANCY_RBAC.md', implode(PHP_EOL, $md) . PHP_EOL);
            File::put($dir . '/WAVE_EXECUTIVA_RH_TENANCY_RBAC.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info('Relatório salvo em docs/operacao/WAVE_EXECUTIVA_RH_TENANCY_RBAC.{md,json}');
        }

        return self::SUCCESS;
    }
}
