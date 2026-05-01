<?php

namespace App\Modules\SaaS\Controllers;

use App\Modules\SaaS\Services\ScaleOpsService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class ScaleController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(private ScaleOpsService $scaleOps)
    {
        $this->middleware(['web', 'tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise']);
    }

    public function index(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $snapshot = $this->scaleOps->snapshot($empresaId);

        return view('enterprise.saas.scale', compact('snapshot'));
    }
}
