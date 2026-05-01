<?php

namespace Tests\Feature\Architecture;

use App\Support\Routing\RouteFileRegistry;
use Tests\TestCase;

class RouteFileRegistryTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('APP_LOAD_LEGACY_ROUTES');
        putenv('APP_LOAD_PATCH_ROUTES');

        parent::tearDown();
    }

    public function test_priority_web_routes_are_present(): void
    {
        $files = RouteFileRegistry::web();

        $this->assertContains(base_path('routes/web.php'), $files);
        $this->assertContains(base_path('routes/web_export_routes.php'), $files);
    }

    public function test_route_registry_does_not_return_duplicates(): void
    {
        $files = RouteFileRegistry::web();

        $this->assertSame(array_values(array_unique($files)), $files);
    }

    public function test_legacy_and_patch_routes_are_disabled_by_default(): void
    {
        $files = RouteFileRegistry::web();
        $directories = RouteFileRegistry::priorityDirectories();

        $this->assertNotContains(base_path('routes/legacy/web_export_routes.php'), $files);
        $this->assertNotContains(base_path('routes/legacy'), $directories);
        $this->assertNotContains(base_path('routes/patches'), $directories);
        $this->assertFalse(RouteFileRegistry::shouldLoadLegacyRoutes());
        $this->assertFalse(RouteFileRegistry::shouldLoadPatchRoutes());
    }

    public function test_legacy_and_patch_routes_can_be_enabled_by_environment(): void
    {
        putenv('APP_LOAD_LEGACY_ROUTES=true');
        putenv('APP_LOAD_PATCH_ROUTES=true');
        $_ENV['APP_LOAD_LEGACY_ROUTES'] = 'true';
        $_ENV['APP_LOAD_PATCH_ROUTES'] = 'true';
        $_SERVER['APP_LOAD_LEGACY_ROUTES'] = 'true';
        $_SERVER['APP_LOAD_PATCH_ROUTES'] = 'true';

        $files = RouteFileRegistry::web();
        $directories = RouteFileRegistry::priorityDirectories();

        $this->assertContains(base_path('routes/legacy/web_export_routes.php'), $files);
        $this->assertContains(base_path('routes/legacy'), $directories);
        $this->assertContains(base_path('routes/patches'), $directories);
        $this->assertTrue(RouteFileRegistry::shouldLoadLegacyRoutes());
        $this->assertTrue(RouteFileRegistry::shouldLoadPatchRoutes());
    }
}
