<?php

namespace App\Modules\SaaS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SaaS\Services\ExecutiveDashboardService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;
use App\Services\RH\RHAdminAuditService;

class ExecutiveDashboardController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(private ExecutiveDashboardService $service, private RHAdminAuditService $audit)
    {
        $this->middleware(['tenant.context', 'enterpriseAccess', 'verificaEmpresa', 'throttle:enterprise']);
    }

    public function index(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $this->audit->log('executive_dashboard.view', 'saas-executive', [
            'mes' => (int) ($request->mes ?: date('m')),
            'ano' => (int) ($request->ano ?: date('Y')),
        ], 'empresa', $empresaId, $empresaId > 0 ? $empresaId : null);

        return view('enterprise.saas.executive', $this->service->build(
            $empresaId,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }
}
