<?php

namespace Tests\Feature\Architecture;

use Tests\TestCase;

class PdvBridgeControllersTest extends TestCase
{
    public function test_offline_bootstrap_controller_depends_on_bridge_service(): void
    {
        $contents = file_get_contents(app_path('Http/Controllers/Pdv/OfflineBootstrapController.php'));

        $this->assertStringContainsString('LegacyOfflineBootstrapBridgeService', $contents);
        $this->assertStringNotContainsString('OfflineBootstrapService', $contents);
    }

    public function test_offline_sync_controller_depends_on_bridge_service(): void
    {
        $contents = file_get_contents(app_path('Http/Controllers/Pdv/OfflineVendaSyncController.php'));

        $this->assertStringContainsString('LegacyOfflineSyncBridgeService', $contents);
        $this->assertStringNotContainsString('OfflineSaleSyncService', $contents);
    }

    public function test_offline_monitor_controller_depends_on_bridge_service(): void
    {
        $contents = file_get_contents(app_path('Http/Controllers/Pdv/OfflineSyncMonitorController.php'));

        $this->assertStringContainsString('LegacyOfflineSyncMonitorBridgeService', $contents);
        $this->assertStringNotContainsString('PdvOfflineSync', $contents);
    }
}
