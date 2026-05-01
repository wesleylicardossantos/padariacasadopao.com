<?php

namespace Tests\Feature\Enterprise;

use Tests\TestCase;

class EnterpriseRouteAuditTest extends TestCase
{
    /**
     * @dataProvider protectedRouteNames
     */
    public function test_enterprise_routes_keep_tenant_and_baseline_hardening(string $name): void
    {
        $route = app('router')->getRoutes()->getByName($name);

        $this->assertNotNull($route, 'Rota não encontrada: ' . $name);

        $middleware = $route->gatherMiddleware();

        $this->assertContains('web', $middleware);
        $this->assertContains('tenant.context', $middleware);
        $this->assertContains('enterpriseAccess', $middleware);
        $this->assertContains('verificaEmpresa', $middleware);
        $this->assertContains('throttle:enterprise', $middleware);
    }

    public static function protectedRouteNames(): array
    {
        return [
            ['enterprise.ai.index'],
            ['enterprise.bi.index'],
            ['enterprise.financeiro.index'],
            ['enterprise.financeiro.operations'],
            ['enterprise.pdv.index'],
            ['enterprise.pdv.mobile'],
            ['enterprise.fiscal.snapshot'],
            ['enterprise.comercial.kpis'],
            ['enterprise.estoque.index'],
            ['enterprise.saas.index'],
        ];
    }
}
