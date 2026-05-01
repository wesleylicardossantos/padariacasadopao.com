<?php

namespace App\Modules\PDV\Services;

use App\Models\PdvOfflineSync;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PdvSyncAuditService
{
    public function summary(int $empresaId): array
    {
        return Cache::remember(sprintf('enterprise:pdv:audit:%s', $empresaId), now()->addSeconds(45), function () use ($empresaId) {
            $query = PdvOfflineSync::query()->where('empresa_id', $empresaId);
            $total = (int) (clone $query)->count();
            $sincronizados = (int) (clone $query)->whereIn('status', ['sincronizado', 'duplicado'])->count();
            $pendentes = (int) (clone $query)->whereIn('status', ['pendente', 'novo', 'sincronizando'])->count();
            $erro = (int) (clone $query)->whereIn('status', ['erro', 'falha'])->count();
            $tentativasAbertas = (int) (clone $query)->where('tentativas', '>', 0)->whereNull('sincronizado_em')->count();
            $healthScore = $total > 0 ? round((($sincronizados) / max($total, 1)) * 100, 2) : 100.0;

            $payload = [
                'total' => $total,
                'sincronizados' => $sincronizados,
                'pendentes' => $pendentes,
                'erro' => $erro,
                'tentativas_abertas' => $tentativasAbertas,
                'ultima_tentativa_em' => optional((clone $query)->orderByDesc('ultima_tentativa_em')->first())->ultima_tentativa_em,
                'ultima_sincronizacao_em' => optional((clone $query)->whereNotNull('sincronizado_em')->orderByDesc('sincronizado_em')->first())->sincronizado_em,
                'health_score' => $healthScore,
                'status_operacao' => $healthScore >= 95 ? 'estavel' : ($healthScore >= 80 ? 'atencao' : 'critico'),
            ];

            Log::channel('pdv')->info('Resumo de sincronizacao do PDV calculado', [
                'empresa_id' => $empresaId,
                'health_score' => $healthScore,
                'pendentes' => $pendentes,
                'erros' => $erro,
            ]);

            return $payload;
        });
    }

    public function pendingItems(int $empresaId, int $limit = 50)
    {
        return PdvOfflineSync::query()
            ->where('empresa_id', $empresaId)
            ->whereNull('sincronizado_em')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    public function divergenceItems(int $empresaId, int $limit = 30)
    {
        return PdvOfflineSync::query()
            ->where('empresa_id', $empresaId)
            ->where(function ($query) {
                $query->whereIn('status', ['erro', 'falha'])
                    ->orWhere(function ($sub) {
                        $sub->whereNull('venda_caixa_id')->whereIn('status', ['sincronizado', 'duplicado']);
                    })
                    ->orWhere('tentativas', '>=', 3);
            })
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(function (PdvOfflineSync $item) {
                return [
                    'id' => $item->id,
                    'uuid_local' => $item->uuid_local,
                    'status' => $item->status,
                    'tentativas' => (int) ($item->tentativas ?? 0),
                    'venda_caixa_id' => $item->venda_caixa_id,
                    'erro' => $item->erro,
                    'ultima_tentativa_em' => optional($item->ultima_tentativa_em)->format('d/m/Y H:i:s'),
                    'sincronizado_em' => optional($item->sincronizado_em)->format('d/m/Y H:i:s'),
                ];
            })
            ->values()
            ->all();
    }

    public function schema(): array
    {
        $table = (new PdvOfflineSync())->getTable();

        return [
            'request_payload' => Schema::hasColumn($table, 'request_payload'),
            'response_payload' => Schema::hasColumn($table, 'response_payload'),
            'erro' => Schema::hasColumn($table, 'erro'),
            'tentativas' => Schema::hasColumn($table, 'tentativas'),
            'sincronizado_em' => Schema::hasColumn($table, 'sincronizado_em'),
            'ultima_tentativa_em' => Schema::hasColumn($table, 'ultima_tentativa_em'),
        ];
    }
}
