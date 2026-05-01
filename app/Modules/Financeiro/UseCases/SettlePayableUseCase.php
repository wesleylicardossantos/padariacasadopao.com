<?php

namespace App\Modules\Financeiro\UseCases;

use App\Models\ContaPagar;
use App\Modules\Financeiro\DTOs\SettleFinancialEntryData;
use App\Modules\Financeiro\Services\FinancialAuditService;
use App\Modules\Financeiro\Services\FinancialCacheService;
use App\Modules\Financeiro\Support\FinancialEntryValidator;
use App\Modules\Financeiro\Support\FinancialMutationGuard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class SettlePayableUseCase
{
    public function __construct(
        private readonly FinancialAuditService $auditService,
        private readonly FinancialMutationGuard $guard,
        private readonly FinancialCacheService $cacheService,
        private readonly FinancialEntryValidator $validator,
    ) {
    }

    public function handle(int $empresaId, int $payableId, SettleFinancialEntryData $data): ContaPagar
    {
        return $this->guard->runWithinGuard(function () use ($empresaId, $payableId, $data) {
            return DB::transaction(function () use ($empresaId, $payableId, $data) {
                $payable = ContaPagar::query()
                    ->where('empresa_id', $empresaId)
                    ->find($payableId);

                if (! $payable) {
                    throw new ModelNotFoundException('Conta a pagar não encontrada para a empresa informada.');
                }

                $before = $payable->toArray();
                $this->validator->validateSettlementAmount($data->paidAmount);

                $payable->fill([
                    'valor_pago' => $data->paidAmount,
                    'data_pagamento' => $data->settlementDate,
                    'tipo_pagamento' => $data->paymentType ?? $payable->tipo_pagamento,
                    'status' => true,
                ]);
                $payable->save();

                $fresh = $payable->fresh();

                $this->auditService->record([
                    'empresa_id' => $empresaId,
                    'filial_id' => $fresh->filial_id,
                    'entidade' => 'conta_pagar',
                    'entidade_id' => $fresh->id,
                    'acao' => 'liquidada',
                    'antes' => $before,
                    'depois' => $fresh->toArray(),
                    'motivo' => $data->observation,
                ]);

                $this->cacheService->forgetByEmpresa($empresaId, $fresh->filial_id);

                return $fresh;
            });
        }, 'finance_settle_payable');
    }
}
