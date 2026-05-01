<?php

namespace Tests\Feature\RH;

use App\Http\Controllers\RHPortalAcessoController;
use App\Http\Controllers\RHPortalFuncionarioController;
use App\Http\Controllers\RHPortalPerfilController;
use Tests\TestCase;

class RHPortalAuditTest extends TestCase
{
    public function test_portal_admin_routes_have_tenant_context(): void
    {
        $route = app('router')->getRoutes()->getByName('rh.portal_perfis.index');
        $this->assertNotNull($route);
        $this->assertContains('tenant.context', $route->gatherMiddleware());

        $route = app('router')->getRoutes()->getByName('rh.portal_externo.configurar');
        $this->assertNotNull($route);
        $this->assertContains('tenant.context', $route->gatherMiddleware());
    }

    public function test_portal_controllers_use_tenant_context_trait(): void
    {
        $expectedTrait = 'App\Support\Tenancy\InteractsWithTenantContext';

        $this->assertContains($expectedTrait, class_uses_recursive(RHPortalAcessoController::class));
        $this->assertContains($expectedTrait, class_uses_recursive(RHPortalFuncionarioController::class));
        $this->assertContains($expectedTrait, class_uses_recursive(RHPortalPerfilController::class));
    }
}
