<?php

namespace Tests\Feature;

use App\Modules\AI\Controllers\GovernanceController as AIGovernanceController;
use App\Modules\BI\Controllers\GovernanceController as BIGovernanceController;
use App\Modules\Comercial\Controllers\GovernanceController as ComercialGovernanceController;
use App\Modules\Estoque\Controllers\GovernanceController as EstoqueGovernanceController;
use App\Modules\Financeiro\Controllers\GovernanceController as FinanceiroGovernanceController;
use App\Modules\Fiscal\Controllers\GovernanceController as FiscalGovernanceController;
use App\Modules\PDV\Controllers\GovernanceController as PdvGovernanceController;
use App\Modules\PDV\Controllers\MobileController as PdvMobileController;
use App\Modules\SaaS\Controllers\GovernanceController as SaaSGovernanceController;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Tests\TestCase;

class EnterpriseGovernanceRuntimeAuditTest extends TestCase
{
    public function test_enterprise_governance_controllers_usam_trait_e_middleware_tenant_context_explicito(): void
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
