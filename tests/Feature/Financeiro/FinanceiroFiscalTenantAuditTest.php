<?php

namespace Tests\Feature\Financeiro;

use App\Http\Controllers\BoletoController;
use App\Http\Controllers\ContigenciaController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\NfseController;
use App\Http\Controllers\RemessaBoletoController;
use Tests\TestCase;

class FinanceiroFiscalTenantAuditTest extends TestCase
{
    /**
     * @dataProvider protectedRouteNames
     */
    public function test_financeiro_fiscal_routes_have_tenant_context(string $name): void
    {
        $route = app('router')->getRoutes()->getByName($name);

        $this->assertNotNull($route, 'Rota não encontrada: ' . $name);

        $middleware = $route->gatherMiddleware();

        $this->assertContains('verificaEmpresa', $middleware);
        $this->assertContains('tenant.context', $middleware);
    }

    public function test_controllers_use_tenant_context_trait(): void
    {
        $expectedTrait = 'App\\Support\\Tenancy\\InteractsWithTenantContext';

        $this->assertContains($expectedTrait, class_uses_recursive(FinanceiroController::class));
        $this->assertContains($expectedTrait, class_uses_recursive(NfseController::class));
        $this->assertContains($expectedTrait, class_uses_recursive(ContigenciaController::class));
        $this->assertContains($expectedTrait, class_uses_recursive(BoletoController::class));
        $this->assertContains($expectedTrait, class_uses_recursive(RemessaBoletoController::class));
    }

    public static function protectedRouteNames(): array
    {
        return [
            ['financeiro.index'],
            ['financeiro.list'],
            ['nfse.index'],
            ['nfse.imprimir'],
            ['boletos.index'],
            ['boletos.print'],
            ['boletos.store-issue'],
            ['remessa-boletos.index'],
            ['remessa.sem-remessa'],
            ['remessa-boletos.download'],
            ['contigencia.index'],
            ['contigencia.desactive'],
            ['compraFiscal.index'],
            ['compraFiscal.store'],
            ['compraFiscal.import'],
        ];
    }
}
