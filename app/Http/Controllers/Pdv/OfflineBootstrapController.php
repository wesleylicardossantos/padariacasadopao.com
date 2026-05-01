<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use App\Modules\PDV\Services\LegacyBridge\LegacyOfflineBootstrapBridgeService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;

class OfflineBootstrapController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(private readonly LegacyOfflineBootstrapBridgeService $service)
    {
        $this->middleware('tenant.context');
    }

    public function __invoke(Request $request)
    {
        return response()->json($this->service->build($request), 200);
    }
}
