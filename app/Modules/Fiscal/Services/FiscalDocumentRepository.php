<?php

namespace App\Modules\Fiscal\Services;

use App\Modules\Fiscal\Models\FiscalDocument;

class FiscalDocumentRepository
{
    public function create(array $payload): FiscalDocument
    {
        return FiscalDocument::query()->create($payload);
    }

    public function update(FiscalDocument $document, array $payload): FiscalDocument
    {
        $document->fill($payload);
        $document->save();

        return $document->fresh();
    }

    public function byEmpresa(int $empresaId, int $limit = 20)
    {
        return FiscalDocument::query()
            ->where('empresa_id', $empresaId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
