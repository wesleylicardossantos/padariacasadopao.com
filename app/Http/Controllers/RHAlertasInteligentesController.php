<?php

namespace App\Http\Controllers;

use App\Services\RHIntelligentAlertsService;
use Illuminate\Http\Request;

class RHAlertasInteligentesController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;
        $mes = (int)($request->mes ?: date('m'));
        $ano = (int)($request->ano ?: date('Y'));

        $alertasGerados = RHIntelligentAlertsService::gerar($empresaId, $mes, $ano);
        $persistidos = RHIntelligentAlertsService::persistir($empresaId, $alertasGerados);
        $alertas = RHIntelligentAlertsService::listar($empresaId, false, 100);

        return view('rh.alertas_inteligentes.index', compact(
            'mes',
            'ano',
            'alertasGerados',
            'persistidos',
            'alertas'
        ));
    }

    public function ler(Request $request, $id)
    {
        RHIntelligentAlertsService::marcarComoLido(request()->empresa_id, $id);
        return redirect('/rh/alertas-inteligentes');
    }
}
