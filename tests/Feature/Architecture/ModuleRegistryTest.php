<?php

namespace Tests\Feature\Architecture;

use App\Modules\ModuleRegistry;
use Tests\TestCase;

class ModuleRegistryTest extends TestCase
{
    public function test_module_registry_lists_expected_modules(): void
    {
        $modules = ModuleRegistry::names();

        $this->assertContains('RH', $modules);
        $this->assertContains('Financeiro', $modules);
        $this->assertContains('PDV', $modules);
        $this->assertContains('SaaS', $modules);
    }

    public function test_module_registry_only_returns_existing_web_route_files(): void
    {
        foreach (ModuleRegistry::webRouteFiles() as $file) {
            $this->assertFileExists($file);
            $this->assertStringEndsWith('Routes/web.php', $file);
        }
    }
}
