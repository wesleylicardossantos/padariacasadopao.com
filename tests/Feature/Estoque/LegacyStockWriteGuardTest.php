<?php

namespace Tests\Feature\Estoque;

use App\Modules\Estoque\Support\LegacyStockWriteGuard;
use Tests\TestCase;

class LegacyStockWriteGuardTest extends TestCase
{
    public function test_guard_allows_nested_operation(): void
    {
        $guard = app(LegacyStockWriteGuard::class);

        $result = $guard->runWithinGuard(function () use ($guard) {
            return [
                'allowed' => $guard->isAllowed(),
                'source' => $guard->source(),
            ];
        }, 'test_guard');

        $this->assertTrue($result['allowed']);
        $this->assertSame('test_guard', $result['source']);
        $this->assertFalse($guard->isAllowed());
    }
}
