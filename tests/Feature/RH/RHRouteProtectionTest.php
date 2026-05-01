<?php

namespace Tests\Feature\RH;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RHRouteProtectionTest extends TestCase
{
    public function test_dossie_routes_have_tenant_context_and_permission_middleware(): void
    {
        $route = Route::getRoutes()->getByName('rh.dossie.documentos.destroy');

        $this->assertNotNull($route);
        $middleware = $route->gatherMiddleware();

        $this->assertContains('tenant.context', $middleware);
        $this->assertContains('rh.permission:rh.dossie.documentos.excluir', $middleware);
    }

    public function test_dashboard_executivo_route_has_explicit_rh_permission(): void
    {
        $route = Route::getRoutes()->getByName('rh.dashboard_executivo.index');

        $this->assertNotNull($route);
        $middleware = $route->gatherMiddleware();

        $this->assertContains('tenant.context', $middleware);
        $this->assertContains('rh.permission:rh.dashboard.executivo', $middleware);
    }
}
