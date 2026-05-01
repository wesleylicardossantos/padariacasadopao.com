<?php

namespace App\Modules\PDV\Services;

use App\Models\PdvOfflineSync;

class PdvReprocessService
{
    public function markForRetry(int $empresaId, int $limit = 100, array $statuses = ['erro', 'erro_recuperavel', 'falha', 'pendente']): int
    {
        $ids = PdvOfflineSync::query()
            ->where('empresa_id', $empresaId)
            ->whereNull('sincronizado_em')
            ->whereIn('status', $statuses)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        $updated = 0;
        PdvOfflineSync::query()->whereIn('id', $ids)->get()->each(function (PdvOfflineSync $item) use (&$updated) {
            $item->status = 'pendente';
            $item->erro = null;
            $item->erro_tipo = null;
            $item->mensagem_amigavel = null;
            $item->ultima_tentativa_em = now();
            $item->save();
            $updated++;
        });

        return $updated;
    }
}
