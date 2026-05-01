<?php

namespace App\Modules\Fiscal\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FiscalAuditService
{
    public function record(array $payload): void
    {
        if (!Schema::hasTable('fiscal_audits')) {
            return;
        }

        DB::table('fiscal_audits')->insert([
            'empresa_id' => $payload['empresa_id'] ?? 0,
            'usuario_id' => $payload['usuario_id'] ?? null,
            'documento_id' => $payload['documento_id'] ?? null,
            'acao' => $payload['acao'] ?? 'fiscal_evento',
            'antes' => isset($payload['antes']) ? json_encode($payload['antes'], JSON_UNESCAPED_UNICODE) : null,
            'depois' => isset($payload['depois']) ? json_encode($payload['depois'], JSON_UNESCAPED_UNICODE) : null,
            'meta' => isset($payload['meta']) ? json_encode($payload['meta'], JSON_UNESCAPED_UNICODE) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
