<?php

namespace App\Http\Controllers;

use App\Modules\RH\Services\RHAnalyticsModuleService;
use Illuminate\Http\Request;

class RHIADecisaoController extends Controller
{
    public function __construct(private RHAnalyticsModuleService $service)
    {
    }

    public function index(Request $request)
    {
        return view('rh.ia_decisao.index', $this->service->montarIADecisao(
            (int) request()->empresa_id,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }
}
