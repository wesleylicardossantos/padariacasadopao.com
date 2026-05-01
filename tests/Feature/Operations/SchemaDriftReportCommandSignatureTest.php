<?php

namespace Tests\Feature\Operations;

use Tests\TestCase;

class SchemaDriftReportCommandSignatureTest extends TestCase
{
    public function test_command_is_registered(): void
    {
        $commands = array_keys($this->app[\Illuminate\Contracts\Console\Kernel::class]->all());

        $this->assertContains('schema:drift-report', $commands);
    }
}
