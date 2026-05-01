<?php

namespace Tests\Feature;

use App\Modules\PDV\Services\OfflineSaleSyncService;
use Illuminate\Http\Request;
use Tests\TestCase;

class OfflineSaleSyncServiceStatusTest extends TestCase
{
    public function test_sync_requires_empresa_id(): void
    {
        $service = app(OfflineSaleSyncService::class);
        $result = $service->sync(Request::create('/pdv/sync', 'POST', []));

        $this->assertSame('erro_fatal', $result['vendas'][0]['status']);
    }
}
