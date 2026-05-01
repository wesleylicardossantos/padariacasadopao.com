<?php

namespace App\Http\Controllers;

use App\Modules\RH\Services\RHAnalyticsModuleService;
use Illuminate\Http\Request;

class RHDreFolhaController extends Controller
{
    public function __construct(private RHAnalyticsModuleService $service)
    {
    }

    public function index(Request $request)
    {
        return view('rh.dre_folha.index', $this->service->montarDreFolha(
            (int) request()->empresa_id,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }
}
