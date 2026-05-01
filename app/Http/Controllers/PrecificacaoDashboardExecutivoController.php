<?php

namespace App\Http\Controllers;

use App\Services\PrecificacaoDashboardExecutivoService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class PrecificacaoDashboardExecutivoController extends Controller
{
    public function __construct(private PrecificacaoDashboardExecutivoService $dashboard)
    {
    }

    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);
        $dashboard = $this->dashboard->montar($empresaId);

        return view('precificacao.dashboard_executivo.index', [
            'title' => 'Dashboard Executivo de Precificação',
            'rotaAtiva' => 'Precificação',
            'dashboard' => $dashboard,
        ]);
    }
}
