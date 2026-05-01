<?php

namespace App\Modules\Financeiro\UseCases;

use App\Modules\Financeiro\Services\FinancialAuditService;
use App\Modules\Financeiro\Services\FinancialCacheService;
use App\Modules\Financeiro\Support\FinancialMutationGuard;
use App\Models\ContaPagar;
use App\Modules\Financeiro\DTOs\RegisterFinancialEntryData;
use Illuminate\Support\Facades\DB;

class RegisterPayableUseCase
{
    public function __construct(
        private readonly FinancialAuditService $auditService,
        private readonly FinancialMutationGuard $guard,
        private readonly FinancialCacheService $cacheService,
    ) {
    }

    public function handle(RegisterFinancialEntryData $data): ContaPagar
    {
        return $this->guard->runWithinGuard(function () use ($data) {
            return DB::transaction(function () use ($data) {
                $entry = ContaPagar::query()->create($data->toPayableAttributes());

                $this->auditService->record([
                    'empresa_id' => $entry->empresa_id,
                    'filial_id' => $entry->filial_id,
                    'entidade' => 'conta_pagar',
                    'entidade_id' => $entry->id,
                    'acao' => 'criada',
                    'depois' => $entry->toArray(),
                ]);

                $this->cacheService->forgetByEmpresa((int) $entry->empresa_id, $entry->filial_id);

                return $entry;
            });
        }, 'finance_register_payable');
    }
}
