<?php

namespace App\Modules\RH\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RH\Services\RHOwnerDashboardService;
use Illuminate\Http\Request;

class PainelDonoController extends Controller
{
    public function __construct(private RHOwnerDashboardService $service)
    {
    }

    public function index(Request $request)
    {
        return view('rh.painel_dono.index', $this->service->montar(
            (int) request()->empresa_id,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }
}
