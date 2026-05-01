<?php

namespace App\Modules\SaaS\Controllers;

use App\Modules\SaaS\Services\ObservabilityService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class ObservabilityController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(private ObservabilityService $observability)
    {
        $this->middleware(['web', 'tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise']);
    }

    public function index(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $summary = $this->observability->summary($empresaId);

        return view('enterprise.saas.observability', compact('summary'));
    }
}
