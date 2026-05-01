<?php

namespace Tests\Feature\Fiscal;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FiscalRoutesTest extends TestCase
{
    public function test_fiscal_enterprise_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('enterprise.fiscal.snapshot'));
        $this->assertTrue(Route::has('enterprise.fiscal.prepare'));
        $this->assertTrue(Route::has('enterprise.fiscal.transmit'));
        $this->assertTrue(Route::has('enterprise.fiscal.cancel'));
        $this->assertTrue(Route::has('enterprise.fiscal.status'));
    }
}
