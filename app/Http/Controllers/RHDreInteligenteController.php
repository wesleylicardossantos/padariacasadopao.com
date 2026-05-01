<?php

namespace App\Http\Controllers;

use App\Modules\RH\Services\RHAnalyticsModuleService;
use Illuminate\Http\Request;

class RHDreInteligenteController extends Controller
{
    public function __construct(private RHAnalyticsModuleService $service)
    {
    }

    public function index(Request $request)
    {
        return view('dre_inteligente.index', $this->service->montarDreInteligente(
            (int) request()->empresa_id,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }
}
