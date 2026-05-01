<?php

namespace App\Http\Controllers;

use App\Services\RHHoleritePdfService;
use Illuminate\Http\Request;

class RHHoleriteController extends Controller
{
    public function __construct(private RHHoleritePdfService $service)
    {
    }

    public function show(Request $request, $id)
    {
        $pdf = $this->service->gerarPdfPorFuncionario(
            $request,
            (int) $id,
            (int) ($request->mes ?: date('m')),
            (int) ($request->ano ?: date('Y')),
        );

        return response($pdf['content'])
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $pdf['filename'] . '"');
    }
}
