<?php

namespace App\Modules\AI\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PdvAnomalyService
{
    public function detect(int $empresaId): array
    {
        return Cache::remember("ai_pdv_anomalies_{$empresaId}", 60, function () use ($empresaId) {
            if (! Schema::hasTable('pdv_offline_syncs')) {
                return [];
            }

            $rows = DB::table('pdv_offline_syncs')
                ->where('empresa_id', $empresaId)
                ->orderByDesc('id')
                ->limit(500)
                ->get();

            $failed = $rows->whereNotNull('erro')->count();
            $retry = $rows->filter(fn ($row) => (int) ($row->tentativas ?? 0) >= 3)->count();
            $unsynced = $rows->filter(fn ($row) => ($row->status ?? null) !== 'sincronizado')->count();

            $anomalies = [];
            if ($failed > 0) {
                $anomalies[] = [
                    'type' => 'sync_error',
                    'severity' => 'alta',
                    'message' => "Existem {$failed} sincronizações com erro registradas recentemente.",
                ];
            }
            if ($retry > 0) {
                $anomalies[] = [
                    'type' => 'retry_pressure',
                    'severity' => 'media',
                    'message' => "Existem {$retry} sincronizações com 3 ou mais tentativas.",
                ];
            }
            if ($unsynced > 20) {
                $anomalies[] = [
                    'type' => 'queue_backlog',
                    'severity' => 'alta',
                    'message' => "Fila operacional do PDV apresenta {$unsynced} registros ainda não sincronizados.",
                ];
            }

            return $anomalies;
        });
    }
}
