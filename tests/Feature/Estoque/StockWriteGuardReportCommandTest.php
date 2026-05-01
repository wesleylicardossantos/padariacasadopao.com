<?php

namespace Tests\Feature\Estoque;

use Tests\TestCase;

class StockWriteGuardReportCommandTest extends TestCase
{
    public function test_command_is_registered(): void
    {
        $this->artisan('stock:write-guard-report')
            ->expectsOutputToContain('Monitoramento ativo:')
            ->assertExitCode(0);
    }
}
