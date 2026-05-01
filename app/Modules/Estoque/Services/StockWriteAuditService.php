<?php

namespace App\Modules\Estoque\Services;

use App\Modules\Estoque\Models\StockWriteAudit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StockWriteAuditService
{
    public function record(array $payload): void
    {
        if (!config('stock_governance.monitor_direct_legacy_writes', true)) {
            return;
        }

        if (Schema::hasTable('stock_write_audits')) {
            StockWriteAudit::query()->create($payload);
        }

        Log::warning('Legacy stock write detected outside ledger guard.', $payload);
    }
}
