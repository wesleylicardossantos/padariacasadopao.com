<?php

namespace App\Modules\RH\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RH\Services\RHFolhaModuleService;
use App\Modules\RH\Support\ResolveEmpresaId;
use Illuminate\Http\Request;

class FolhaController extends Controller
{
    public function __construct(private RHFolhaModuleService $service)
    {
    }

    public function index(Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        $payload = $this->service->montarFolha(
            $empresaId,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
            $request->nome
        );

        return view('rh.folha.index', $payload);
    }

    public function recibo($id, Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        $payload = $this->service->montarRecibo(
            $empresaId,
            (int) $id,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        );

        return view('rh.folha.recibo', $payload);
    }

    public function financeiro(Request $request)
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);

        $payload = $this->service->montarResumoFinanceiro(
            $empresaId,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        );

        return view('rh.folha.financeiro', $payload);
    }
}
