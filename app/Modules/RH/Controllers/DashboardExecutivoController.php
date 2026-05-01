<?php

namespace App\Modules\RH\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RH\Services\RHAnalyticsModuleService;
use Illuminate\Http\Request;

class DashboardExecutivoController extends Controller
{
    public function __construct(private RHAnalyticsModuleService $service)
    {
    }

    public function index(Request $request)
    {
        return view('dashboard_executivo.index', $this->service->montarDashboardExecutivo(
            (int) request()->empresa_id,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }
}
