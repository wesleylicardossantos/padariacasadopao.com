<?php

namespace App\Modules\Financeiro\Services\LegacyBridge;

use App\Models\CategoriaConta;
use App\Models\ContaPagar;
use App\Models\Fornecedor;
use App\Modules\Financeiro\Data\FinanceFilterData;
use App\Modules\Financeiro\DTOs\RegisterFinancialEntryData;
use App\Modules\Financeiro\DTOs\SettleFinancialEntryData;
use App\Modules\Financeiro\Repositories\PayableRepository;
use App\Modules\Financeiro\Services\FinancialCacheService;
use App\Modules\Financeiro\UseCases\DeletePayableUseCase;
use App\Modules\Financeiro\UseCases\RegisterPayableUseCase;
use App\Modules\Financeiro\UseCases\SettlePayableUseCase;
use App\Modules\Financeiro\UseCases\UpdatePayableUseCase;
use App\Modules\Financeiro\Support\FinancialEntryValidator;
use App\Support\RuntimeConfig;
use App\Support\Tenancy\ResolveEmpresaId;
use Illuminate\Http\Request;

class LegacyPayableBridgeService
{
    public function __construct(
        protected PayableRepository $repository,
        protected SettlePayableUseCase $settlePayableUseCase,
        protected RegisterPayableUseCase $registerPayableUseCase,
        protected UpdatePayableUseCase $updatePayableUseCase,
        protected DeletePayableUseCase $deletePayableUseCase,
        protected FinancialCacheService $cacheService,
        protected FinancialEntryValidator $validator,
    ) {
    }

    public function indexPayload(Request $request): array
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $filter = FinanceFilterData::fromRequest($request, 'fornecedor_id');

        return [
            'data' => $this->repository->paginatedIndex($empresaId, $filter, RuntimeConfig::pagination()),
            'fornecedores' => Fornecedor::where('empresa_id', $empresaId)->get(),
            'filial_id' => $filter->filialId,
        ];
    }

    public function createPayload(Request $request): array
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        return [
            'categorias' => CategoriaConta::where('empresa_id', $empresaId)->where('tipo', 'pagar')->orderBy('nome')->get(),
            'fornecedores' => Fornecedor::where('empresa_id', $empresaId)->get(),
        ];
    }

    public function editPayload(Request $request, ContaPagar $item): array
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        return [
            'categorias' => CategoriaConta::where('empresa_id', $empresaId)->where('tipo', 'pagar')->orderBy('nome')->get(),
            'item' => $item,
            'fornecedores' => Fornecedor::where('empresa_id', $empresaId)->get(),
        ];
    }

    public function create(Request $request): ContaPagar
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $main = $this->registerPayableUseCase->handle(
            RegisterFinancialEntryData::fromRequest($request, $empresaId, 'fornecedor_id')
        );

        if ($request->boolean('status')) {
            $main = $this->settleMainPayable($request, $main);
        }

        foreach ((array) $request->dt_recorrencia as $index => $dataVencimento) {
            if (! $dataVencimento) {
                continue;
            }

            $recurringRequest = clone $request;
            $recurringRequest->merge([
                'data_vencimento' => $dataVencimento,
                'valor_integral' => $request->valor_recorrencia[$index] ?? 0,
                'filial_id' => $request->filial_id,
            ]);

            $recurring = $this->registerPayableUseCase->handle(
                RegisterFinancialEntryData::fromRequest($recurringRequest, $empresaId, 'fornecedor_id')
            );

            if ($request->boolean('status')) {
                $this->settleMainPayable($recurringRequest, $recurring);
            }
        }

        return $main;
    }

    public function update(Request $request, ContaPagar $item): ContaPagar
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $this->validator->assertPayableBelongsToEmpresa($item, $empresaId);

        return $this->updatePayableUseCase->handle($item, [
            'filial_id' => $request->filial_id,
            'categoria_id' => $request->categoria_id,
            'fornecedor_id' => $request->fornecedor_id,
            'referencia' => $request->referencia,
            'valor_integral' => $request->valor_integral,
            'data_vencimento' => $request->data_vencimento,
            'tipo_pagamento' => $request->tipo_pagamento,
        ]);
    }

    public function markAsPaid(Request $request, ContaPagar $item): ContaPagar
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $this->validator->assertPayableBelongsToEmpresa($item, $empresaId);

        return $this->settlePayableUseCase->handle(
            $empresaId,
            (int) $item->id,
            SettleFinancialEntryData::fromRequest($request)
        );
    }

    public function destroy(Request $request, ContaPagar $item): void
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $this->validator->assertPayableBelongsToEmpresa($item, $empresaId);

        $this->deletePayableUseCase->handle($item);
    }

    private function settleMainPayable(Request $request, ContaPagar $item): ContaPagar
    {
        return $this->settlePayableUseCase->handle(
            (int) $item->empresa_id,
            (int) $item->id,
            new SettleFinancialEntryData(
                settlementDate: (string) ($request->data_pagamento ?: $request->data_vencimento),
                paidAmount: (float) __convert_value_bd($request->valor_integral),
                paymentType: $request->tipo_pagamento,
                observation: $request->observacao,
            )
        );
    }
}
