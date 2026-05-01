<?php

namespace App\Modules\Financeiro\UseCases;

use App\Models\ContaReceber;
use App\Modules\Financeiro\Services\FinancialAuditService;
use App\Modules\Financeiro\Services\FinancialCacheService;
use App\Modules\Financeiro\Support\FinancialEntryValidator;
use App\Modules\Financeiro\Support\FinancialMutationGuard;
use App\Support\Tenancy\ScopedFilialResolver;
use Illuminate\Support\Facades\DB;

class UpdateReceivableUseCase
{
    public function __construct(
        private readonly FinancialAuditService $auditService,
        private readonly FinancialMutationGuard $guard,
        private readonly FinancialCacheService $cacheService,
        private readonly FinancialEntryValidator $validator,
    ) {
    }

    public function handle(ContaReceber $receivable, array $attributes): ContaReceber
    {
        return $this->guard->runWithinGuard(function () use ($receivable, $attributes) {
            return DB::transaction(function () use ($receivable, $attributes) {
                $before = $receivable->toArray();
                $payload = $this->sanitizeAttributes($attributes, (int) $receivable->empresa_id);

                $receivable->fill($payload);
                $receivable->save();

                $fresh = $receivable->fresh();

                $this->auditService->record([
                    'empresa_id' => (int) $fresh->empresa_id,
                    'filial_id' => $fresh->filial_id,
                    'entidade' => 'conta_receber',
                    'entidade_id' => $fresh->id,
                    'acao' => 'atualizada',
                    'antes' => $before,
                    'depois' => $fresh->toArray(),
                ]);

                $this->cacheService->forgetByEmpresa((int) $fresh->empresa_id, $fresh->filial_id);

                return $fresh;
            });
        }, 'finance_update_receivable');
    }

    private function sanitizeAttributes(array $attributes, int $empresaId): array
    {
        $payload = $this->validator->validateReceivablePayload($empresaId, $attributes);
        $payload['filial_id'] = ScopedFilialResolver::resolveForEmpresa($empresaId, $payload['filial_id'] ?? null);

        return $payload;
    }
}
