<?php

namespace Tests\Feature;

use App\Support\Routing\RouteFileRegistry;
use Tests\TestCase;

class ProjectInventoryCommandTest extends TestCase
{
    public function test_route_registry_exposes_priority_directories(): void
    {
        $this->assertIsArray(RouteFileRegistry::priorityDirectories());
    }
}
