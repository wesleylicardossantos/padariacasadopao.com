<?php

namespace App\Modules\RH\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RH\Services\RHFolhaModuleService;
use App\Modules\RH\Support\ResolveEmpresaId;
use Illuminate\Http\Request;

class ResumoFinanceiroController extends Controller
{
    public function __construct(private RHFolhaModuleService $service)
    {
    }

    public function index(Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        return view('rh.folha.resumo_financeiro', $this->service->montarResumoDetalhado(
            $empresaId,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }
}
