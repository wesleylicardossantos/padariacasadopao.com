<?php

namespace App\Modules\Comercial\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comercial\DTOs\CustomerLifecycleFilterData;
use App\Modules\Comercial\Repositories\CustomerRepository;
use App\Modules\Comercial\Services\SalesMetricsService;
use App\Support\Tenancy\ResolveEmpresaId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly SalesMetricsService $metrics,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $filters = CustomerLifecycleFilterData::fromRequest($request, $empresaId);

        $paginator = $this->customers->paginate(
            empresaId: $filters->empresaId,
            search: $filters->search,
            documento: $filters->documento,
            perPage: $filters->perPage,
        );

        return response()->json([
            'success' => true,
            'filters' => [
                'search' => $filters->search,
                'cpf_cnpj' => $filters->documento,
                'filial_id' => $filters->filialId,
            ],
            'data' => $paginator,
        ]);
    }

    public function snapshot(Request $request): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $filialId = $request->input('filial_id', 'todos');

        return response()->json([
            'success' => true,
            'portfolio' => [
                'clientes_total' => $this->customers->queryByEmpresa($empresaId)->count(),
                'clientes_ativos' => $this->customers->queryByEmpresa($empresaId)->where(function ($query) {
                    $query->whereNull('inativo')->orWhere('inativo', false)->orWhere('inativo', 0);
                })->count(),
                'clientes_inativos' => $this->customers->queryByEmpresa($empresaId)->where('inativo', true)->count(),
            ],
            'sales' => [
                'today' => $this->metrics->today($empresaId, $filialId),
                'month' => $this->metrics->currentMonth($empresaId, $filialId),
            ],
        ]);
    }
}
