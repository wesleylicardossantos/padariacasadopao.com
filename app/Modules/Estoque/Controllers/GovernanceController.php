<?php

namespace App\Modules\Estoque\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Estoque\DTOs\StockMovementData;
use App\Modules\Estoque\Http\Requests\StockAdjustmentRequest;
use App\Modules\Estoque\Http\Requests\StockEntryRequest;
use App\Modules\Estoque\Http\Requests\StockExitRequest;
use App\Modules\Estoque\Services\InventorySnapshotService;
use App\Modules\Estoque\Services\StockGovernanceReportService;
use App\Modules\Estoque\Services\StockLedgerService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GovernanceController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(
        private readonly InventorySnapshotService $snapshotService,
        private readonly StockLedgerService $ledgerService,
        private readonly StockGovernanceReportService $governanceReportService,
    ) {
        $this->middleware('tenant.context');
    }

    public function index(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $snapshot = $this->snapshotService->summary($empresaId);

        return view('enterprise.estoque.index', compact('snapshot', 'empresaId'));
    }

    public function snapshot(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);

        return response()->json($this->snapshotService->summary($empresaId));
    }

    public function entry(StockEntryRequest $request): RedirectResponse
    {
        $this->ledgerService->entry(StockMovementData::fromRequest($request));

        return back()->with('flash_sucesso', 'Entrada de estoque registrada com sucesso.');
    }

    public function exit(StockExitRequest $request): RedirectResponse
    {
        $this->ledgerService->exit(StockMovementData::fromRequest($request));

        return back()->with('flash_sucesso', 'Saída de estoque registrada com sucesso.');
    }

    public function adjustment(StockAdjustmentRequest $request): RedirectResponse
    {
        $this->ledgerService->adjustment(StockMovementData::fromRequest($request));

        return back()->with('flash_sucesso', 'Ajuste de estoque registrado com sucesso.');
    }


    public function reconcile(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);

        \Artisan::call('stock:reconcile', [
            'empresa_id' => $empresaId,
            '--filial_id' => $request->input('filial_id'),
            '--limit' => 200,
            '--write' => true,
        ]);

        return response()->json([
            'success' => true,
            'exit_code' => \Artisan::output(),
            'message' => 'Reconciliação executada. Consulte os artefatos em docs/operacao.',
        ]);
    }

    public function guardReport(): JsonResponse
    {
        return response()->json($this->governanceReportService->generate(true));
    }
}

