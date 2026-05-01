<?php

namespace App\Console\Commands;

use App\Http\Controllers\BoletoController;
use App\Http\Controllers\ContigenciaController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\NfseController;
use App\Http\Controllers\RemessaBoletoController;
use App\Http\Controllers\RHPortalAcessoController;
use App\Http\Controllers\RHPortalFuncionarioController;
use App\Http\Controllers\RHPortalPerfilController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\Pdv\OfflineBootstrapController;
use App\Http\Controllers\Pdv\OfflineSyncMonitorController;
use App\Http\Controllers\Pdv\OfflineVendaSyncController;
use App\Http\Controllers\Pdv\ProdutoController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class RefactorTenantRuntimeHardeningAuditCommand extends Command
{
    protected $signature = 'refactor:tenant-runtime-hardening-audit {--write : Persiste o relatório em docs/operacao}';

    protected $description = 'Audita controllers com TenantContext endurecido no runtime via middleware explícito.';

    public function handle(): int
    {
        $controllers = [
            RHPortalFuncionarioController::class,
            RHPortalPerfilController::class,
            RHPortalAcessoController::class,
            FinanceiroController::class,
            NfseController::class,
            BoletoController::class,
            RemessaBoletoController::class,
            ContigenciaController::class,
            StockController::class,
            ProdutoController::class,
            OfflineBootstrapController::class,
            OfflineVendaSyncController::class,
            OfflineSyncMonitorController::class,
        ];

        $checks = [];
        foreach ($controllers as $controller) {
            $traits = class_uses_recursive($controller);
            $reflection = new ReflectionClass($controller);
            $source = File::get($reflection->getFileName());
            $checks[] = [
                'controller' => $controller,
                'uses_tenant_trait' => in_array('App\\Support\\Tenancy\\InteractsWithTenantContext', $traits, true),
                'has_explicit_middleware' => str_contains($source, "\$this->middleware('tenant.context')")
                    || str_contains($source, '$this->middleware("tenant.context")'),
            ];
        }

        $summary = [
            'generated_at' => now()->toDateTimeString(),
            'tenant_context_alias_registered' => array_key_exists('tenant.context', app('router')->getMiddleware()),
            'controllers' => $checks,
        ];

        foreach ($checks as $check) {
            $this->line(sprintf(
                '- %s | trait=%s middleware=%s',
                class_basename($check['controller']),
                $check['uses_tenant_trait'] ? 'sim' : 'nao',
                $check['has_explicit_middleware'] ? 'sim' : 'nao'
            ));
        }

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/operacao'));

            $md = [];
            $md[] = '# Wave Runtime Hardening de TenantContext';
            $md[] = '';
            $md[] = '- Gerado em: ' . $summary['generated_at'];
            $md[] = '- Alias tenant.context registrado: ' . ($summary['tenant_context_alias_registered'] ? 'sim' : 'nao');
            $md[] = '';
            $md[] = '## Controllers auditados';
            $md[] = '';
            $md[] = '| Controller | InteractsWithTenantContext | Middleware explícito |';
            $md[] = '|---|---|---|';
            foreach ($checks as $check) {
                $md[] = sprintf(
                    '| %s | %s | %s |',
                    class_basename($check['controller']),
                    $check['uses_tenant_trait'] ? 'sim' : 'nao',
                    $check['has_explicit_middleware'] ? 'sim' : 'nao'
                );
            }
            $md[] = '';
            $md[] = '- Nesta wave, controllers críticos que já usavam TenantContext passaram a forçar tenant.context também no runtime via construtor.';
            $md[] = '- Isso reduz dependência da ordem de carregamento de arquivos de rota e estabiliza o tenancy em ambiente legado.';

            File::put(base_path('docs/operacao/WAVE_TENANT_RUNTIME_HARDENING_2026-04-10.md'), implode(PHP_EOL, $md) . PHP_EOL);
            File::put(base_path('docs/operacao/WAVE_TENANT_RUNTIME_HARDENING_2026-04-10.json'), json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            File::put(base_path('docs/operacao/STATUS_REAL_REFATORACAO_WAVE_TENANT_RUNTIME_HARDENING_2026-04-10.md'), implode(PHP_EOL, [
                '# Status real da wave Runtime Hardening TenantContext 2026-04-10',
                '',
                '- Adição de tenant.context explícito em construtor dos controllers críticos que já consumiam InteractsWithTenantContext.',
                '- Redução da dependência exclusiva da pilha de middleware das rotas em módulos legados e híbridos.',
                '- Criação do comando refactor:tenant-runtime-hardening-audit para auditoria contínua dessa camada.',
            ]) . PHP_EOL);
        }

        return self::SUCCESS;
    }
}
