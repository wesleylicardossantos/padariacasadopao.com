<?php

namespace App\Modules\Financeiro\UseCases;

use App\Models\ContaReceber;
use App\Modules\Financeiro\Services\FinancialAuditService;
use App\Modules\Financeiro\Services\FinancialCacheService;
use App\Modules\Financeiro\Support\FinancialMutationGuard;
use Illuminate\Support\Facades\DB;

class DeleteReceivableUseCase
{
    public function __construct(
        private readonly FinancialAuditService $auditService,
        private readonly FinancialMutationGuard $guard,
        private readonly FinancialCacheService $cacheService,
    ) {
    }

    public function handle(ContaReceber $receivable): void
    {
        $this->guard->runWithinGuard(function () use ($receivable) {
            DB::transaction(function () use ($receivable) {
                $before = $receivable->toArray();
                $empresaId = (int) $receivable->empresa_id;
                $filialId = $receivable->filial_id;
                $id = $receivable->id;

                $receivable->delete();

                $this->auditService->record([
                    'empresa_id' => $empresaId,
                    'filial_id' => $filialId,
                    'entidade' => 'conta_receber',
                    'entidade_id' => $id,
                    'acao' => 'removida',
                    'antes' => $before,
                ]);

                $this->cacheService->forgetByEmpresa($empresaId, $filialId);
            });
        }, 'finance_delete_receivable');
    }
}
