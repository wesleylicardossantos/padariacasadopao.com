<?php

namespace Tests\Feature\Hardening;

use Tests\TestCase;

class HardeningCommandsTest extends TestCase
{
    public function test_hardening_final_command_is_registered(): void
    {
        $this->artisan('hardening:final-report')
            ->assertExitCode(0);
    }

    public function test_deadcode_candidates_command_is_registered(): void
    {
        $this->artisan('deadcode:candidates-report')
            ->assertExitCode(0);
    }
}
