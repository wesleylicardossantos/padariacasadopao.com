<?php

namespace App\Modules\RH\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RH\Services\RHAnalyticsModuleService;
use App\Modules\RH\Support\ResolveEmpresaId;
use Illuminate\Http\Request;

class DrePreditivoController extends Controller
{
    public function __construct(private RHAnalyticsModuleService $service)
    {
    }

    public function index(Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        return view('dre_preditivo.index', $this->service->montarDrePreditivo(
            $empresaId,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
            (int) ($request->sim_contratacoes ?: 0),
            (float) ($request->sim_salario ?: 0),
        ));
    }
}
