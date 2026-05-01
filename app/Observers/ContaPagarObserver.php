<?php

namespace App\Observers;

use App\Models\ContaPagar;
use App\Modules\Financeiro\Services\FinancialAuditService;
use App\Modules\Financeiro\Support\FinancialMutationGuard;
use Illuminate\Support\Facades\Log;

class ContaPagarObserver
{
    public function __construct(
        private readonly FinancialMutationGuard $guard,
        private readonly FinancialAuditService $auditService,
    ) {
    }

    public function updating(ContaPagar $conta): void
    {
        $criticalFields = ['status', 'valor_pago', 'data_pagamento', 'tipo_pagamento'];
        $criticalDirty = array_intersect($criticalFields, array_keys($conta->getDirty()));

        if ($criticalDirty === []) {
            return;
        }

        $guardAllowed = $this->guard->isAllowed();
        $payload = [
            'empresa_id' => (int) $conta->empresa_id,
            'entidade' => 'conta_pagar',
            'entidade_id' => (int) $conta->id,
            'acao' => $guardAllowed ? 'mutacao_critica_permitida' : 'mutacao_critica_direta_detectada',
            'antes' => array_intersect_key($conta->getOriginal(), array_flip($criticalFields)),
            'depois' => array_intersect_key($conta->getAttributes(), array_flip($criticalFields)),
            'motivo' => $guardAllowed
                ? 'Mutação crítica executada via fluxo canônico financeiro.'
                : 'Mutação crítica detectada fora do fluxo canônico financeiro.',
            'usuario_id' => function_exists('get_id_user') ? (int) get_id_user() : null,
        ];

        if (config('finance_governance.monitor_direct_mutations', true)) {
            $this->auditService->record($payload);
            Log::channel(config('finance_governance.log_channel', config('logging.default')))
                ->warning('Direct payable settlement mutation detected.', [
                    'guard_allowed' => $guardAllowed,
                    'guard_source' => $this->guard->source(),
                    'dirty_fields' => array_values($criticalDirty),
                    'entidade_id' => $conta->id,
                    'empresa_id' => $conta->empresa_id,
                    'request_path' => request()?->path(),
                ]);
        }

        if (! $guardAllowed && config('finance_governance.block_direct_settlement_mutations', true)) {
            throw new \DomainException('Mutação crítica de conta a pagar bloqueada fora do fluxo canônico financeiro.');
        }
    }
}
