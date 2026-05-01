<?php

namespace Tests\Feature;

use Tests\TestCase;

class PDVEstoqueTenantAuditTest extends TestCase
{
    public function test_command_is_registered(): void
    {
        $this->assertTrue(class_exists(\App\Console\Commands\RefactorPdvEstoqueTenantAuditCommand::class));
    }
}
