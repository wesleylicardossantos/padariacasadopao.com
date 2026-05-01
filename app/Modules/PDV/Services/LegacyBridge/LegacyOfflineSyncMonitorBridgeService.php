<?php

namespace App\Modules\PDV\Services\LegacyBridge;

use App\Models\PdvOfflineSync;
use App\Modules\PDV\Data\SyncMonitorFilterData;
use App\Modules\PDV\Services\PdvReprocessService;
use App\Modules\PDV\Services\PdvSyncAuditService;
use App\Support\Tenancy\ResolveEmpresaId;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class LegacyOfflineSyncMonitorBridgeService
{
    public function __construct(
        private readonly PdvSyncAuditService $audit,
        private readonly PdvReprocessService $reprocess,
    ) {
    }

    public function viewPayload(Request $request): array
    {
        $empresaId = $this->resolveEmpresaId($request);
        $payload = $this->payload($empresaId, SyncMonitorFilterData::fromRequest($request));

        return [
            'title' => 'PDV Offline - Monitor de Sincronização',
            'rotaAtiva' => 'PDV Offline',
            'empresaId' => $empresaId,
            'payload' => $payload,
            'schema' => $payload['schema'],
        ];
    }

    public function dataPayload(Request $request): array
    {
        return $this->payload(
            $this->resolveEmpresaId($request),
            SyncMonitorFilterData::fromRequest($request),
        );
    }

    public function retryPending(Request $request): array
    {
        $empresaId = $this->resolveEmpresaId($request);
        $affected = $this->reprocess->markForRetry($empresaId, 500, ['pendente', 'sincronizando']);

        return [
            'empresa_id' => $empresaId,
            'affected' => $affected,
            'message' => "Pendentes preparados para reenvio: {$affected}",
        ];
    }

    public function retryErrors(Request $request): array
    {
        $empresaId = $this->resolveEmpresaId($request);
        $affected = $this->reprocess->markForRetry($empresaId, 500, ['erro', 'falha']);

        return [
            'empresa_id' => $empresaId,
            'affected' => $affected,
            'message' => "Registros com erro preparados para reenvio: {$affected}",
        ];
    }

    private function payload(int $empresaId, SyncMonitorFilterData $filters): array
    {
        $baseQuery = PdvOfflineSync::query()->where('empresa_id', $empresaId);

        if ($filters->status !== '') {
            $baseQuery->where('status', $filters->status);
        }

        if ($filters->uuidLocal !== '') {
            $baseQuery->where('uuid_local', 'like', '%' . $filters->uuidLocal . '%');
        }

        $grouped = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentes = $this->paginateRecentes($baseQuery, $filters->perPage);

        $summary = $this->audit->summary($empresaId);

        return [
            'empresa_id' => $empresaId,
            'schema' => $this->audit->schema(),
            'filtros' => $filters->toArray(),
            'metricas' => [
                'pendentes' => (int) ($grouped['pendente'] ?? 0),
                'sincronizando' => (int) ($grouped['sincronizando'] ?? 0),
                'sincronizadas' => (int) ($grouped['sincronizado'] ?? 0),
                'duplicadas' => (int) ($grouped['duplicado'] ?? 0),
                'com_erro' => (int) ($grouped['erro'] ?? 0),
                'ultima_sincronizacao_em' => $this->formatDateTime(data_get($summary, 'ultima_sincronizacao_em')),
            ],
            'ultimos_erros' => $this->latestErrors($empresaId),
            'lista' => $recentes->toArray(),
            'consultado_em' => now()->format('d/m/Y H:i:s'),
        ];
    }

    private function paginateRecentes($baseQuery, int $perPage): LengthAwarePaginator
    {
        return (clone $baseQuery)
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->through(function (PdvOfflineSync $item) {
                return [
                    'id' => $item->id,
                    'uuid_local' => $item->uuid_local,
                    'status' => $item->status,
                    'venda_caixa_id' => $item->venda_caixa_id,
                    'tentativas' => (int) ($item->tentativas ?? 0),
                    'sincronizado_em' => optional($item->sincronizado_em)->format('d/m/Y H:i:s'),
                    'ultima_tentativa_em' => optional($item->ultima_tentativa_em)->format('d/m/Y H:i:s'),
                    'erro' => $item->erro,
                    'request_payload' => $item->request_payload,
                    'response_payload' => $item->response_payload,
                    'created_at' => optional($item->created_at)->format('d/m/Y H:i:s'),
                    'updated_at' => optional($item->updated_at)->format('d/m/Y H:i:s'),
                ];
            });
    }

    private function latestErrors(int $empresaId): array
    {
        return PdvOfflineSync::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('status', ['erro', 'falha'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(function (PdvOfflineSync $item) {
                return [
                    'uuid_local' => $item->uuid_local,
                    'erro' => $item->erro,
                    'tentativas' => (int) ($item->tentativas ?? 0),
                    'ultima_tentativa_em' => optional($item->ultima_tentativa_em)->format('d/m/Y H:i:s'),
                ];
            })
            ->values()
            ->all();
    }

    private function resolveEmpresaId(Request $request): int
    {
        $empresaId = ResolveEmpresaId::fromRequest($request);
        abort_if($empresaId <= 0, 403, 'Empresa não identificada para o monitor do PDV offline.');

        return $empresaId;
    }

    private function formatDateTime(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i:s');
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return null;
    }
}
