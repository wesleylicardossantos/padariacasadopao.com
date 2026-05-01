<?php

namespace App\Modules\SaaS\Controllers;

use App\Http\Controllers\Controller;
use App\Services\RH\RHAdminAuditService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;
use App\Modules\SaaS\Services\PremiumAnalyticsService;

class PremiumController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(
        private PremiumAnalyticsService $service,
        private RHAdminAuditService $audit,
    ) {
        $this->middleware('tenant.context');
    }

    public function index(Request $request)
    {
        $empresaId = $this->tenantEmpresaId($request);
        $mes = (int) ($request->input('mes') ?: now()->month);
        $ano = (int) ($request->input('ano') ?: now()->year);

        $this->audit->log('premium_dashboard.view', 'saas-premium', [
            'empresa_id' => $empresaId,
            'mes' => $mes,
            'ano' => $ano,
        ]);

        return view('enterprise.saas.premium', $this->service->build($empresaId, $mes, $ano));
    }
}
