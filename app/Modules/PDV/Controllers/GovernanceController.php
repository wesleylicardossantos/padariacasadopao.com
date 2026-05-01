<?php

namespace App\Modules\PDV\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PDV\Services\PdvReprocessService;
use App\Modules\PDV\Services\PdvSyncAuditService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceController extends Controller
{
    use InteractsWithTenantContext;

    protected PdvSyncAuditService $audit;
    protected PdvReprocessService $reprocess;

    public function __construct(PdvSyncAuditService $audit, PdvReprocessService $reprocess)
    {
        $this->middleware('tenant.context');
        $this->audit = $audit;
        $this->reprocess = $reprocess;
    }

    public function index(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $payload = $this->buildPayload($empresaId);

        return view('enterprise.pdv.index', $payload);
    }

    public function audit(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);

        return response()->json($this->buildPayload($empresaId));
    }

    public function reprocess(Request $request): JsonResponse
    {
        $empresaId = $this->tenantEmpresaId($request);
        $limit = max(1, min((int) $request->get('limit', 100), 500));

        return response()->json([
            'success' => true,
            'module' => 'PDV',
            'updated' => $this->reprocess->markForRetry($empresaId, $limit),
            'audit' => $this->audit->summary($empresaId),
        ]);
    }

    protected function buildPayload(int $empresaId): array
    {
        return [
            'success' => true,
            'module' => 'PDV',
            'empresa_id' => $empresaId,
            'audit' => $this->audit->summary($empresaId),
            'pending_items' => $this->audit->pendingItems($empresaId, 20),
            'divergences' => $this->audit->divergenceItems($empresaId, 20),
            'schema' => $this->audit->schema(),
            'updated_at' => now()->format('d/m/Y H:i:s'),
        ];
    }
}
