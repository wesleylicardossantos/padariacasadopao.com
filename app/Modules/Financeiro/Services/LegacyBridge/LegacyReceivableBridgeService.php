<?php

namespace App\Modules\Financeiro\Services\LegacyBridge;

use App\Models\Acessor;
use App\Models\CategoriaConta;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\Funcionario;
use App\Models\GrupoCliente;
use App\Models\Pais;
use App\Modules\Financeiro\Data\FinanceFilterData;
use App\Modules\Financeiro\DTOs\RegisterFinancialEntryData;
use App\Modules\Financeiro\DTOs\SettleFinancialEntryData;
use App\Modules\Financeiro\Repositories\ReceivableRepository;
use App\Modules\Financeiro\Services\FinancialCacheService;
use App\Modules\Financeiro\UseCases\DeleteReceivableUseCase;
use App\Modules\Financeiro\UseCases\RegisterReceivableUseCase;
use App\Modules\Financeiro\UseCases\SettleReceivableUseCase;
use App\Modules\Financeiro\UseCases\UpdateReceivableUseCase;
use App\Modules\Financeiro\Support\FinancialEntryValidator;
use App\Support\RuntimeConfig;
use App\Support\Tenancy\ResolveEmpresaId;
use Illuminate\Http\Request;

class LegacyReceivableBridgeService
{
    public function __construct(
        protected ReceivableRepository $repository,
        protected SettleReceivableUseCase $settleReceivableUseCase,
        protected RegisterReceivableUseCase $registerReceivableUseCase,
        protected UpdateReceivableUseCase $updateReceivableUseCase,
        protected DeleteReceivableUseCase $deleteReceivableUseCase,
        protected FinancialCacheService $cacheService,
        protected FinancialEntryValidator $validator,
    ) {
    }

    public function indexPayload(Request $request): array
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $filter = FinanceFilterData::fromRequest($request, 'cliente_id');

        return [
            'data' => $this->repository->paginatedIndex($empresaId, $filter, RuntimeConfig::pagination()),
            'clientes' => Cliente::where('empresa_id', $empresaId)->get(),
            'filial_id' => $filter->filialId,
        ];
    }

    public function createPayload(Request $request): array
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        return [
            'categorias' => CategoriaConta::where('empresa_id', $empresaId)->where('tipo', 'receber')->orderBy('nome')->get(),
            'cidades' => Cidade::all(),
            'paises' => Pais::all(),
            'grupos' => GrupoCliente::where('empresa_id', $empresaId)->get(),
            'acessores' => Acessor::where('empresa_id', $empresaId)->get(),
            'funcionarios' => Funcionario::where('empresa_id', $empresaId)->get(),
            'clientes' => Cliente::where('empresa_id', $empresaId)->get(),
        ];
    }

    public function editPayload(Request $request, ContaReceber $item): array
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        return [
            'categorias' => CategoriaConta::where('empresa_id', $empresaId)->where('tipo', 'receber')->orderBy('nome')->get(),
            'item' => $item,
            'paises' => Pais::all(),
            'grupos' => GrupoCliente::where('empresa_id', $empresaId)->get(),
            'acessores' => Acessor::where('empresa_id', $empresaId)->get(),
            'funcionarios' => Funcionario::where('empresa_id', $empresaId)->get(),
        ];
    }

    public function create(Request $request): ContaReceber
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $main = $this->registerReceivableUseCase->handle(
            RegisterFinancialEntryData::fromRequest($request, $empresaId, 'cliente_id')
        );

        if ($request->boolean('status')) {
            $main = $this->settleMainReceivable($request, $main);
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

            $recurring = $this->registerReceivableUseCase->handle(
                RegisterFinancialEntryData::fromRequest($recurringRequest, $empresaId, 'cliente_id')
            );

            if ($request->boolean('status')) {
                $this->settleMainReceivable($recurringRequest, $recurring);
            }
        }

        return $main;
    }

    public function update(Request $request, ContaReceber $item): ContaReceber
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $this->validator->assertReceivableBelongsToEmpresa($item, $empresaId);

        return $this->updateReceivableUseCase->handle($item, [
            'filial_id' => $request->filial_id,
            'categoria_id' => $request->categoria_id,
            'cliente_id' => $request->cliente_id,
            'referencia' => $request->referencia,
            'valor_integral' => $request->valor_integral,
            'data_vencimento' => $request->data_vencimento,
            'tipo_pagamento' => $request->tipo_pagamento,
            'observacao' => $request->observacao,
        ]);
    }

    public function markAsPaid(Request $request, ContaReceber $item): ContaReceber
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $this->validator->assertReceivableBelongsToEmpresa($item, $empresaId);

        return $this->settleReceivableUseCase->handle(
            $empresaId,
            (int) $item->id,
            SettleFinancialEntryData::fromRequest($request)
        );
    }

    public function destroy(Request $request, ContaReceber $item): void
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $this->validator->assertReceivableBelongsToEmpresa($item, $empresaId);

        $this->deleteReceivableUseCase->handle($item);
    }

    private function settleMainReceivable(Request $request, ContaReceber $item): ContaReceber
    {
        return $this->settleReceivableUseCase->handle(
            (int) $item->empresa_id,
            (int) $item->id,
            new SettleFinancialEntryData(
                settlementDate: (string) ($request->data_recebimento ?: $request->data_vencimento),
                paidAmount: (float) __convert_value_bd($request->valor_integral),
                paymentType: $request->tipo_pagamento,
                observation: $request->observacao,
            )
        );
    }
}
