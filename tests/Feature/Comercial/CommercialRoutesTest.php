<?php

namespace Tests\Feature\Comercial;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CommercialRoutesTest extends TestCase
{
    public function test_enterprise_commercial_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('enterprise.comercial.customers.upsert'));
        $this->assertTrue(Route::has('enterprise.comercial.sales.create'));
        $this->assertTrue(Route::has('enterprise.comercial.orders.create'));
        $this->assertTrue(Route::has('enterprise.comercial.budgets.create'));
    }
}
