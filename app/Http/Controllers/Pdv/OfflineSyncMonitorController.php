<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use App\Modules\PDV\Services\LegacyBridge\LegacyOfflineSyncMonitorBridgeService;
use Illuminate\Http\JsonResponse;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;

class OfflineSyncMonitorController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(private readonly LegacyOfflineSyncMonitorBridgeService $service)
    {
        $this->middleware('tenant.context');
    }

    public function index(Request $request)
    {
        return view('pdv_offline.monitor', $this->service->viewPayload($request));
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json($this->service->dataPayload($request));
    }

    public function reenviarPendentes(Request $request)
    {
        $result = $this->service->retryPending($request);

        return redirect()
            ->route('pdv.offline.monitor', ['empresa_id' => $result['empresa_id']])
            ->with('success', $result['message']);
    }

    public function reenviarErros(Request $request)
    {
        $result = $this->service->retryErrors($request);

        return redirect()
            ->route('pdv.offline.monitor', ['empresa_id' => $result['empresa_id']])
            ->with('success', $result['message']);
    }
}
