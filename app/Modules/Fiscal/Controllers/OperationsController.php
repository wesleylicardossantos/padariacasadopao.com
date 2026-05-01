<?php

namespace App\Modules\Fiscal\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fiscal\Http\Requests\CancelFiscalDocumentRequest;
use App\Modules\Fiscal\Http\Requests\PrepareFiscalDocumentRequest;
use App\Modules\Fiscal\Models\FiscalDocument;
use App\Modules\Fiscal\Services\FiscalFacadeService;
use App\Support\Tenancy\ResolveEmpresaId;
use Illuminate\Http\JsonResponse;

class OperationsController extends Controller
{
    public function __construct(
        private readonly FiscalFacadeService $facade,
    ) {
    }

    public function prepare(PrepareFiscalDocumentRequest $request): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $document = $this->facade->prepare($empresaId, $request->validated(), auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Documento fiscal preparado com sucesso.',
            'data' => $document,
        ], 201);
    }

    public function transmit(PrepareFiscalDocumentRequest $request): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $document = $this->facade->prepare($empresaId, $request->validated(), auth()->id());
        $document = $this->facade->transmit($document, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Documento fiscal transmitido pela facade estável.',
            'data' => $document,
        ]);
    }

    public function cancel(CancelFiscalDocumentRequest $request, int $id): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $document = FiscalDocument::query()->where('empresa_id', $empresaId)->findOrFail($id);
        $document = $this->facade->cancel($document, (string) $request->input('reason'), auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Documento fiscal cancelado com sucesso.',
            'data' => $document,
        ]);
    }

    public function status(CancelFiscalDocumentRequest $request, int $id): JsonResponse
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        $document = FiscalDocument::query()->where('empresa_id', $empresaId)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->facade->status($document),
        ]);
    }
}
