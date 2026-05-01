<?php

namespace App\Modules\Financeiro\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinancialAuditService
{
    public function record(array $payload): void
    {
        if (!Schema::hasTable('financial_audits')) {
            return;
        }

        $allowed = array_flip(Schema::getColumnListing('financial_audits'));
        $beforePayload = isset($payload['antes']) ? json_encode($payload['antes'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        $afterPayload = isset($payload['depois']) ? json_encode($payload['depois'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        $action = $payload['acao'] ?? null;
        $entity = $payload['entidade'] ?? null;
        $reason = $payload['motivo'] ?? null;
        $userId = $payload['usuario_id'] ?? Auth::id();

        $attributes = array_filter([
            'empresa_id' => $payload['empresa_id'] ?? null,
            'filial_id' => $payload['filial_id'] ?? null,
            'usuario_id' => $userId,
            'user_id' => $userId,
            'entidade' => $entity,
            'entity_type' => $entity,
            'entidade_id' => $payload['entidade_id'] ?? null,
            'entity_id' => $payload['entidade_id'] ?? null,
            'acao' => $action,
            'action' => $action,
            'antes' => $beforePayload,
            'before_payload' => $beforePayload,
            'depois' => $afterPayload,
            'after_payload' => $afterPayload,
            'motivo' => $reason,
            'reason' => $reason,
            'created_at' => now(),
            'updated_at' => now(),
        ], fn ($value) => $value !== null);

        DB::table('financial_audits')->insert(array_intersect_key($attributes, $allowed));
    }
}
