<?php

namespace App\Http\Controllers;

use App\Modules\RH\Services\RHFolhaModuleService;
use App\Modules\RH\Support\ResolveEmpresaId;
use Illuminate\Http\Request;
use App\Modules\RH\Support\RHContext;

class RHFolhaController extends Controller
{
    public function __construct(private RHFolhaModuleService $service)
    {
    }

    public function index(Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        return view('rh.folha.index', $this->service->montarFolha(
            $empresaId,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
            $request->nome,
        ));
    }

    public function recibo($id, Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        return view('rh.folha.recibo', $this->service->montarRecibo(
            $empresaId,
            (int) $id,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }

    public function financeiro(Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        return view('rh.folha.financeiro', $this->service->montarResumoFinanceiro(
            $empresaId,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        ));
    }
}
