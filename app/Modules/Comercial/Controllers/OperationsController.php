<?php

namespace App\Modules\Comercial\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comercial\Http\Requests\CreateBudgetRequest;
use App\Modules\Comercial\Http\Requests\CreateSaleRequest;
use App\Modules\Comercial\Http\Requests\CreateSalesOrderRequest;
use App\Modules\Comercial\Http\Requests\UpsertCustomerRequest;
use App\Modules\Comercial\UseCases\CreateBudgetUseCase;
use App\Modules\Comercial\UseCases\CreateCommercialSaleUseCase;
use App\Modules\Comercial\UseCases\CreateSalesOrderUseCase;
use App\Modules\Comercial\UseCases\UpsertCustomerUseCase;
use App\Support\Tenancy\ResolveEmpresaId;
use Illuminate\Http\JsonResponse;

class OperationsController extends Controller
{
    public function __construct(
        private readonly UpsertCustomerUseCase $upsertCustomer,
        private readonly CreateCommercialSaleUseCase $createSale,
        private readonly CreateSalesOrderUseCase $createOrder,
        private readonly CreateBudgetUseCase $createBudget,
    ) {
    }

    public function upsertCustomer(UpsertCustomerRequest $request): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $customer = $this->upsertCustomer->handle($empresaId, $request->validated(), auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Cliente salvo com sucesso.',
            'data' => $customer,
        ], empty($request->input('id')) ? 201 : 200);
    }

    public function createSale(CreateSaleRequest $request): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $sale = $this->createSale->handle($empresaId, $request->validated(), auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Venda criada com sucesso.',
            'data' => $sale,
        ], 201);
    }

    public function createOrder(CreateSalesOrderRequest $request): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $order = $this->createOrder->handle($empresaId, $request->validated(), auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Pedido criado com sucesso.',
            'data' => $order,
        ], 201);
    }

    public function createBudget(CreateBudgetRequest $request): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $budget = $this->createBudget->handle($empresaId, $request->validated(), auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Orçamento criado com sucesso.',
            'data' => $budget,
        ], 201);
    }
}
