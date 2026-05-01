<?php

namespace Tests\Feature\Architecture\Financeiro;

use App\Http\Controllers\ContaPagarController;
use App\Http\Controllers\ContaReceberController;
use App\Modules\Financeiro\Services\LegacyBridge\LegacyPayableBridgeService;
use App\Modules\Financeiro\Services\LegacyBridge\LegacyReceivableBridgeService;
use ReflectionClass;
use Tests\TestCase;

class LegacyBridgeControllersTest extends TestCase
{
    public function test_conta_receber_controller_depends_on_bridge_service(): void
    {
        $reflection = new ReflectionClass(ContaReceberController::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertSame(LegacyReceivableBridgeService::class, $constructor->getParameters()[0]->getType()?->getName());
    }

    public function test_conta_pagar_controller_depends_on_bridge_service(): void
    {
        $reflection = new ReflectionClass(ContaPagarController::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertSame(LegacyPayableBridgeService::class, $constructor->getParameters()[0]->getType()?->getName());
    }

    public function test_financeiro_bridge_services_exist_in_modular_namespace(): void
    {
        $this->assertTrue(class_exists(LegacyReceivableBridgeService::class));
        $this->assertTrue(class_exists(LegacyPayableBridgeService::class));
    }
}
