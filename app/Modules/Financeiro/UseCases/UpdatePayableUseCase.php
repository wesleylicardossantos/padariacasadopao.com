<?php

namespace App\Modules\Financeiro\UseCases;

use App\Models\ContaPagar;
use App\Modules\Financeiro\Services\FinancialAuditService;
use App\Modules\Financeiro\Services\FinancialCacheService;
use App\Modules\Financeiro\Support\FinancialEntryValidator;
use App\Modules\Financeiro\Support\FinancialMutationGuard;
use App\Support\Tenancy\ScopedFilialResolver;
use Illuminate\Support\Facades\DB;

class UpdatePayableUseCase
{
    public function __construct(
        private readonly FinancialAuditService $auditService,
        private readonly FinancialMutationGuard $guard,
        private readonly FinancialCacheService $cacheService,
        private readonly FinancialEntryValidator $validator,
    ) {
    }

    public function handle(ContaPagar $payable, array $attributes): ContaPagar
    {
        return $this->guard->runWithinGuard(function () use ($payable, $attributes) {
            return DB::transaction(function () use ($payable, $attributes) {
                $before = $payable->toArray();
                $payload = $this->sanitizeAttributes($attributes, (int) $payable->empresa_id);

                $payable->fill($payload);
                $payable->save();

                $fresh = $payable->fresh();

                $this->auditService->record([
                    'empresa_id' => (int) $fresh->empresa_id,
                    'filial_id' => $fresh->filial_id,
                    'entidade' => 'conta_pagar',
                    'entidade_id' => $fresh->id,
                    'acao' => 'atualizada',
                    'antes' => $before,
                    'depois' => $fresh->toArray(),
                ]);

                $this->cacheService->forgetByEmpresa((int) $fresh->empresa_id, $fresh->filial_id);

                return $fresh;
            });
        }, 'finance_update_payable');
    }

    private function sanitizeAttributes(array $attributes, int $empresaId): array
    {
        $payload = $this->validator->validatePayablePayload($empresaId, $attributes);
        $payload['filial_id'] = ScopedFilialResolver::resolveForEmpresa($empresaId, $payload['filial_id'] ?? null);
        unset($payload['observacao']);

        return $payload;
    }
}
