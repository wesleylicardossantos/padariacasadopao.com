<?php

namespace App\Modules\Financeiro\UseCases;

use App\Models\ContaReceber;
use App\Modules\Financeiro\DTOs\SettleFinancialEntryData;
use App\Modules\Financeiro\Services\FinancialAuditService;
use App\Modules\Financeiro\Services\FinancialCacheService;
use App\Modules\Financeiro\Support\FinancialEntryValidator;
use App\Modules\Financeiro\Support\FinancialMutationGuard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class SettleReceivableUseCase
{
    public function __construct(
        private readonly FinancialAuditService $auditService,
        private readonly FinancialMutationGuard $guard,
        private readonly FinancialCacheService $cacheService,
        private readonly FinancialEntryValidator $validator,
    ) {
    }

    public function handle(int $empresaId, int $receivableId, SettleFinancialEntryData $data): ContaReceber
    {
        return $this->guard->runWithinGuard(function () use ($empresaId, $receivableId, $data) {
            return DB::transaction(function () use ($empresaId, $receivableId, $data) {
                $receivable = ContaReceber::query()
                    ->where('empresa_id', $empresaId)
                    ->find($receivableId);

                if (! $receivable) {
                    throw new ModelNotFoundException('Conta a receber não encontrada para a empresa informada.');
                }

                $before = $receivable->toArray();
                $this->validator->validateSettlementAmount($data->paidAmount);

                $receivable->fill([
                    'valor_recebido' => $data->paidAmount,
                    'data_recebimento' => $data->settlementDate,
                    'tipo_pagamento' => $data->paymentType ?? $receivable->tipo_pagamento,
                    'observacao' => trim(implode(' | ', array_filter([$receivable->observacao, $data->observation]))),
                    'status' => true,
                ]);
                $receivable->save();

                $fresh = $receivable->fresh();

                $this->auditService->record([
                    'empresa_id' => $empresaId,
                    'filial_id' => $fresh->filial_id,
                    'entidade' => 'conta_receber',
                    'entidade_id' => $fresh->id,
                    'acao' => 'liquidada',
                    'antes' => $before,
                    'depois' => $fresh->toArray(),
                    'motivo' => $data->observation,
                ]);

                $this->cacheService->forgetByEmpresa($empresaId, $fresh->filial_id);

                return $fresh;
            });
        }, 'finance_settle_receivable');
    }
}
