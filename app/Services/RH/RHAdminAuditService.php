<?php

namespace App\Services\RH;

use App\Models\RH\RHAdminActionAudit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RHAdminAuditService
{
    public function log(string $acao, string $modulo, array $payload = [], ?string $referenciaTipo = null, mixed $referenciaId = null, ?int $empresaId = null, ?int $usuarioId = null): void
    {
        if (!Schema::hasTable('rh_admin_action_audits')) {
            return;
        }

        $columns = Schema::getColumnListing('rh_admin_action_audits');
        $has = static fn (string $column): bool => in_array($column, $columns, true);

        $data = [];
        if ($has('empresa_id')) {
            $data['empresa_id'] = $empresaId ?: (int) data_get(session('user_logged'), 'empresa', 0);
        }
        if ($has('usuario_id')) {
            $data['usuario_id'] = $usuarioId ?: (function_exists('get_id_user') ? (int) get_id_user() : null);
        }
        if ($has('acao')) {
            $data['acao'] = $acao;
        }
        if ($has('modulo')) {
            $data['modulo'] = $modulo;
        }

        // Compatibilidade com os dois desenhos já encontrados no projeto/banco:
        // - migration nova: referencia_tipo / referencia_id
        // - banco real Hostgator: alvo_tipo / alvo_id
        if ($has('referencia_tipo')) {
            $data['referencia_tipo'] = $referenciaTipo;
        }
        if ($has('referencia_id')) {
            $data['referencia_id'] = $referenciaId;
        }
        if ($has('alvo_tipo')) {
            $data['alvo_tipo'] = $referenciaTipo;
        }
        if ($has('alvo_id')) {
            $data['alvo_id'] = is_numeric($referenciaId) ? (int) $referenciaId : null;
        }

        if ($has('payload_json')) {
            $data['payload_json'] = $payload;
        }
        if ($has('ip')) {
            $data['ip'] = request()?->ip();
        }
        if ($has('user_agent')) {
            $data['user_agent'] = substr((string) request()?->userAgent(), 0, 1000);
        }

        try {
            RHAdminActionAudit::query()->create($data);
        } catch (\Throwable $e) {
            // Auditoria não pode derrubar dashboard/portal/RH em produção.
            Log::warning('RH admin audit skipped after schema mismatch.', [
                'erro' => $e->getMessage(),
                'acao' => $acao,
                'modulo' => $modulo,
            ]);
        }
    }
}
