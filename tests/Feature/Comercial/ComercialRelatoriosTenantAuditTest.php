<?php

namespace Tests\Feature\Comercial;

use Tests\TestCase;

class ComercialRelatoriosTenantAuditTest extends TestCase
{
    public function test_route_names_have_tenant_context_middleware(): void
    {
        $routes = app('router')->getRoutes()->getRoutesByName();

        foreach (['relatorios.index', 'produtos.index', 'vendas.index', 'pedidos.index', 'dre.index'] as $name) {
            $route = $routes[$name] ?? null;
            $this->assertNotNull($route, "Route {$name} should exist");
            $this->assertContains('tenant.context', $route->gatherMiddleware(), "Route {$name} should use tenant.context");
        }
    }
}
