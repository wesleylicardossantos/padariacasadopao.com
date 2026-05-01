<?php

namespace Tests\Feature;

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
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Tests\TestCase;

class TenantRuntimeHardeningAuditTest extends TestCase
{
    public function test_controllers_usam_trait_e_middleware_tenant_context_explicito(): void
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

        foreach ($controllers as $controller) {
            $traits = class_uses_recursive($controller);
            $this->assertContains('App\\Support\\Tenancy\\InteractsWithTenantContext', $traits, $controller . ' sem trait de tenant');

            $reflection = new ReflectionClass($controller);
            $source = File::get($reflection->getFileName());
            $this->assertTrue(
                str_contains($source, "\$this->middleware('tenant.context')") || str_contains($source, '$this->middleware("tenant.context")'),
                $controller . ' sem middleware tenant.context explícito'
            );
        }
    }
}
