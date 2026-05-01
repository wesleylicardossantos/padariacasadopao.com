<?php

namespace Tests\Unit\RH;

use App\Modules\RH\Support\Enums\EmployeeStatus;
use PHPUnit\Framework\TestCase;

class EmployeeStatusTest extends TestCase
{
    public function test_active_values_are_detected(): void
    {
        $this->assertTrue(EmployeeStatus::isActiveValue(1));
        $this->assertTrue(EmployeeStatus::isActiveValue('S'));
        $this->assertTrue(EmployeeStatus::isActiveValue(null));
        $this->assertFalse(EmployeeStatus::isActiveValue(0));
    }

    public function test_to_ativo_column_maps_archived_to_zero(): void
    {
        $this->assertSame(0, EmployeeStatus::toAtivoColumn(EmployeeStatus::ARCHIVED));
        $this->assertSame(1, EmployeeStatus::toAtivoColumn(EmployeeStatus::ACTIVE));
    }
}
