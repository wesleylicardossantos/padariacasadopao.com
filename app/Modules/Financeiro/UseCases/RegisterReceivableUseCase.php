<?php

namespace App\Modules\Financeiro\UseCases;

use App\Models\ContaReceber;
use App\Modules\Financeiro\DTOs\RegisterFinancialEntryData;
use App\Modules\Financeiro\Services\FinancialAuditService;
use App\Modules\Financeiro\Services\FinancialCacheService;
use App\Modules\Financeiro\Support\FinancialMutationGuard;
use Illuminate\Support\Facades\DB;

class RegisterReceivableUseCase
{
    public function __construct(
        private readonly FinancialAuditService $auditService,
        private readonly FinancialMutationGuard $guard,
        private readonly FinancialCacheService $cacheService,
    ) {
    }

    public function handle(RegisterFinancialEntryData $data): ContaReceber
    {
        return $this->guard->runWithinGuard(function () use ($data) {
            return DB::transaction(function () use ($data) {
                $receivable = ContaReceber::query()->create($data->toReceivableAttributes());

                $this->auditService->record([
                    'empresa_id' => $receivable->empresa_id,
                    'filial_id' => $receivable->filial_id,
                    'entidade' => 'conta_receber',
                    'entidade_id' => $receivable->id,
                    'acao' => 'criada',
                    'depois' => $receivable->toArray(),
                ]);

                $this->cacheService->forgetByEmpresa((int) $receivable->empresa_id, $receivable->filial_id);

                return $receivable;
            });
        }, 'finance_register_receivable');
    }
}
