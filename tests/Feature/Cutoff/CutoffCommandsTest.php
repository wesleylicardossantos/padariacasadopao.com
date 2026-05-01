<?php

namespace Tests\Feature\Cutoff;

use Tests\TestCase;

class CutoffCommandsTest extends TestCase
{
    public function test_legacy_cutoff_readiness_command_is_registered(): void
    {
        $this->artisan('legacy:cutoff-readiness-report')
            ->assertExitCode(0);
    }

    public function test_performance_baseline_report_command_is_registered(): void
    {
        $this->artisan('performance:baseline-report')
            ->assertExitCode(0);
    }
}
