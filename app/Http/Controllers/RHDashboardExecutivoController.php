<?php

namespace App\Http\Controllers;

use App\Modules\RH\Services\RHAnalyticsModuleService;
use App\Services\RH\RHDossieAutomationService;
use Illuminate\Http\Request;

class RHDashboardExecutivoController extends Controller
{
    public function __construct(private RHAnalyticsModuleService $service, private RHDossieAutomationService $automation)
    {
        $this->middleware('rh.permission:rh.dashboard.executivo')->only(['index']);
    }

    public function index(Request $request)
    {
        $empresaId = (int) request()->empresa_id;
        if ($empresaId > 0) {
            $this->automation->syncEmpresa($empresaId);
        }

        return view('dashboard_executivo.index', $this->service->montarDashboardExecutivo(
            $empresaId,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }
}
