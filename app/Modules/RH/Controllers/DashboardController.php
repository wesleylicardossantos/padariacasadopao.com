<?php

namespace App\Modules\RH\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RH\Services\RHDashboardModuleService;
use App\Modules\RH\Support\RHContext;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private RHDashboardModuleService $service)
    {
    }

    public function index(Request $request)
    {
        $mes = $request->integer('mes') ?: null;
        $ano = $request->integer('ano') ?: null;

        return view('rh.dashboard', $this->service->montarDashboard(
            RHContext::empresaId($request),
            $mes,
            $ano,
        ));
    }
}
