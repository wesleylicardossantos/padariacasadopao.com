<?php

namespace App\Http\Controllers;

use App\Modules\RH\Services\RHAnalyticsModuleService;
use Illuminate\Http\Request;

class RHDrePreditivoController extends Controller
{
    public function __construct(private RHAnalyticsModuleService $service)
    {
    }

    public function index(Request $request)
    {
        return view('dre_preditivo.index', $this->service->montarDrePreditivo(
            (int) request()->empresa_id,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
            (int) ($request->sim_contratacoes ?: 0),
            (float) ($request->sim_salario ?: 0),
        ));
    }
}
