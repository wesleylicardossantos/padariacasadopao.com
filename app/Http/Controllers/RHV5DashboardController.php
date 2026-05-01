<?php

namespace App\Http\Controllers;

use App\Modules\RH\Services\RHDashboardModuleService;
use App\Modules\RH\Support\RHContext;

class RHV5DashboardController extends Controller
{
    public function __construct(private RHDashboardModuleService $service)
    {
    }

    public function index()
    {
        return view('rh.dashboard', $this->service->montarDashboard(RHContext::empresaId(request())));
    }
}
