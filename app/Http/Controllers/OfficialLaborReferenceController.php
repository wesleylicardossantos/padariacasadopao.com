<?php

namespace App\Http\Controllers;

use App\Services\OfficialLaborReferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficialLaborReferenceController extends Controller
{
    public function __construct(private OfficialLaborReferenceService $service)
    {
    }

    public function cbo(Request $request): JsonResponse
    {
        $this->service->ensureSynced();

        $items = $this->service->searchCbo($request->query('q'), (int) $request->query('limit', 20))
            ->map(fn ($item) => [
                'codigo' => $item->codigo,
                'titulo' => $item->titulo,
                'label' => $item->codigo . ' - ' . $item->titulo,
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    public function funcoes(Request $request): JsonResponse
    {
        $this->service->ensureSynced();

        $items = $this->service->searchFunctions($request->query('q'), (int) $request->query('limit', 20))
            ->map(fn ($item) => [
                'codigo' => $item->codigo,
                'descricao' => $item->descricao,
                'cbo_codigo' => $item->cbo_codigo,
                'label' => $item->descricao . ($item->cbo_codigo ? ' - CBO ' . $item->cbo_codigo : ''),
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    public function sync(Request $request): JsonResponse
    {
        $result = $this->service->syncAll($request->boolean('force'));

        return response()->json([
            'message' => 'Base oficial sincronizada com sucesso.',
            'data' => $result,
        ]);
    }
}
