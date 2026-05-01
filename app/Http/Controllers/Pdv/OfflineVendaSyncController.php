<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use App\Modules\PDV\Services\LegacyBridge\LegacyOfflineSyncBridgeService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;

class OfflineVendaSyncController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(private readonly LegacyOfflineSyncBridgeService $service)
    {
        $this->middleware('tenant.context');
    }

    public function sincronizar(Request $request)
    {
        return response()->json($this->service->sync($request), 200);
    }

    public function status(Request $request)
    {
        return response()->json($this->service->status($request), 200);
    }

    public function dashboard(Request $request)
    {
        return response()->json($this->service->dashboard($request), 200);
    }
}
