<?php

namespace App\Modules\Financeiro\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Financeiro\DTOs\RegisterFinancialEntryData;
use App\Modules\Financeiro\DTOs\SettleFinancialEntryData;
use App\Modules\Financeiro\Services\CashFlowService;
use App\Modules\Financeiro\Services\FinancialClosingService;
use App\Modules\Financeiro\Services\FinancialMetricsService;
use App\Modules\Financeiro\UseCases\RegisterPayableUseCase;
use App\Modules\Financeiro\UseCases\RegisterReceivableUseCase;
use App\Modules\Financeiro\UseCases\SettlePayableUseCase;
use App\Modules\Financeiro\UseCases\SettleReceivableUseCase;
use App\Support\Tenancy\ResolveEmpresaId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    public function __construct(
        private readonly RegisterReceivableUseCase $registerReceivable,
        private readonly RegisterPayableUseCase $registerPayable,
        private readonly SettleReceivableUseCase $settleReceivable,
        private readonly SettlePayableUseCase $settlePayable,
        private readonly FinancialMetricsService $metrics,
        private readonly CashFlowService $cashFlow,
        private readonly FinancialClosingService $closing,
    ) {
    }

    public function overview(Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $filialId = $request->get('filial_id', 'todos');

        return view('enterprise.financeiro.operations', [
            'snapshot' => $this->metrics->snapshot($empresaId, $filialId),
            'aging' => $this->metrics->aging($empresaId, $filialId),
            'cashFlow' => $this->cashFlow->projection($empresaId, $filialId, 6),
            'closure' => $this->closing->monthlyClosure($empresaId, $filialId),
            'empresa_id' => $empresaId,
            'filial_id' => $filialId,
        ]);
    }

    public function registerReceivable(Request $request): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $entry = $this->registerReceivable->handle(RegisterFinancialEntryData::fromRequest($request, $empresaId, 'cliente_id'));

        return response()->json(['success' => true, 'message' => 'Conta a receber criada.', 'data' => $entry], 201);
    }

    public function registerPayable(Request $request): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $entry = $this->registerPayable->handle(RegisterFinancialEntryData::fromRequest($request, $empresaId, 'fornecedor_id'));

        return response()->json(['success' => true, 'message' => 'Conta a pagar criada.', 'data' => $entry], 201);
    }

    public function settleReceivable(Request $request, int $id): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $entry = $this->settleReceivable->handle($empresaId, $id, SettleFinancialEntryData::fromRequest($request));

        return response()->json(['success' => true, 'message' => 'Conta a receber liquidada.', 'data' => $entry]);
    }

    public function settlePayable(Request $request, int $id): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $entry = $this->settlePayable->handle($empresaId, $id, SettleFinancialEntryData::fromRequest($request));

        return response()->json(['success' => true, 'message' => 'Conta a pagar liquidada.', 'data' => $entry]);
    }
}
