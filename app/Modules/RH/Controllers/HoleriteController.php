<?php

namespace App\Modules\RH\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RH\Services\RHFolhaModuleService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class HoleriteController extends Controller
{
    public function __construct(private RHFolhaModuleService $service)
    {
    }

    public function show(Request $request, int $id)
    {
        $payload = $this->service->montarRecibo(
            (int) request()->empresa_id,
            $id,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        );

        $html = view('rh.holerite.pdf', [
            'empresa' => $payload['empresa'],
            'funcionario' => $payload['funcionario'],
            'mes' => $payload['mes'],
            'ano' => $payload['ano'],
            'salarioBase' => $payload['salarioBase'],
            'eventos' => $payload['eventos'],
            'descontos' => $payload['descontos'],
            'proventos' => $payload['proventos'],
            'liquido' => $payload['liquido'],
            'valores' => $payload['valores'],
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('dpi', 96);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="holerite-' . $id . '-' . $payload['mes'] . '-' . $payload['ano'] . '.pdf"');
    }
}
