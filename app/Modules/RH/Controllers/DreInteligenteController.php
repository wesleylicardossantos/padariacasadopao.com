<?php

namespace App\Modules\RH\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RH\Services\RHAnalyticsModuleService;
use App\Modules\RH\Support\ResolveEmpresaId;
use Illuminate\Http\Request;

class DreInteligenteController extends Controller
{
    public function __construct(private RHAnalyticsModuleService $service)
    {
    }

    public function index(Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        return view('dre_inteligente.index', $this->service->montarDreInteligente(
            $empresaId,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }
}
