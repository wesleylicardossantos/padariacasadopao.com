<?php

namespace App\Modules\Financeiro\UseCases;

use App\Models\ContaPagar;
use App\Modules\Financeiro\Services\FinancialAuditService;
use App\Modules\Financeiro\Services\FinancialCacheService;
use App\Modules\Financeiro\Support\FinancialMutationGuard;
use Illuminate\Support\Facades\DB;

class DeletePayableUseCase
{
    public function __construct(
        private readonly FinancialAuditService $auditService,
        private readonly FinancialMutationGuard $guard,
        private readonly FinancialCacheService $cacheService,
    ) {
    }

    public function handle(ContaPagar $payable): void
    {
        $this->guard->runWithinGuard(function () use ($payable) {
            DB::transaction(function () use ($payable) {
                $before = $payable->toArray();
                $empresaId = (int) $payable->empresa_id;
                $filialId = $payable->filial_id;
                $id = $payable->id;

                $payable->delete();

                $this->auditService->record([
                    'empresa_id' => $empresaId,
                    'filial_id' => $filialId,
                    'entidade' => 'conta_pagar',
                    'entidade_id' => $id,
                    'acao' => 'removida',
                    'antes' => $before,
                ]);

                $this->cacheService->forgetByEmpresa($empresaId, $filialId);
            });
        }, 'finance_delete_payable');
    }
}
