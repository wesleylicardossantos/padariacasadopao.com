<?php

namespace App\Modules\PDV\Services;

use App\Helpers\StockMove;
use App\Models\Empresa;
use App\Models\FaturaFrenteCaixa;
use App\Models\ItemVendaCaixa;
use App\Models\PdvOfflineSync;
use App\Models\Produto;
use App\Models\VendaCaixa;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OfflineSaleSyncService
{
    private const STATUS_PENDENTE = 'pendente';
    private const STATUS_SINCRONIZANDO = 'sincronizando';
    private const STATUS_SINCRONIZADO = 'sincronizado';
    private const STATUS_DUPLICADO = 'duplicado';
    private const STATUS_CONFLITO_PAYLOAD = 'conflito_payload';
    private const STATUS_ERRO_RECUPERAVEL = 'erro_recuperavel';
    private const STATUS_ERRO_FATAL = 'erro_fatal';
    private const MAX_TENTATIVAS_PADRAO = 10;

    public function sync(Request $request): array
    {
        $empresaId = TenantContext::empresaId($request);
        $usuarioId = TenantContext::userId($request) ?? 0;
        $vendas = $this->extractSalesPayload($request);

        if ($empresaId <= 0) {
            return $this->invalidBatchResponse('empresa_id é obrigatório.');
        }

        if ($usuarioId <= 0) {
            return $this->invalidBatchResponse('pdv_usuario_id é obrigatório.', $empresaId);
        }

        if (! $this->assertInfrastructureIsReady()) {
            return $this->invalidBatchResponse('Estrutura do PDV offline não está pronta. Execute as migrations/SQL do módulo pdv_offline_syncs.');
        }

        $retorno = [];
        foreach ($vendas as $vendaPayload) {
            $retorno[] = $this->syncOne($empresaId, $usuarioId, (array) $vendaPayload);
        }

        return [
            'empresa_id' => $empresaId,
            'sincronizadas' => collect($retorno)->where('status', self::STATUS_SINCRONIZADO)->count(),
            'duplicadas' => collect($retorno)->where('status', self::STATUS_DUPLICADO)->count(),
            'conflitos' => collect($retorno)->where('status', self::STATUS_CONFLITO_PAYLOAD)->count(),
            'com_erro' => collect($retorno)->filter(fn (array $item) => str_starts_with((string) ($item['status'] ?? ''), 'erro_'))->count(),
            'pendentes' => collect($retorno)->where('status', self::STATUS_PENDENTE)->count(),
            'vendas' => $retorno,
            'processado_em' => now()->toDateTimeString(),
        ];
    }

    public function status(Request $request): array
    {
        $empresaId = TenantContext::empresaId($request);
        $uuids = array_filter((array) $request->input('uuids', []));

        $query = PdvOfflineSync::query()->where('empresa_id', $empresaId);
        if (!empty($uuids)) {
            $query->whereIn('uuid_local', $uuids);
        }

        $baseQuery = clone $query;
        $items = $query->latest('id')->limit(200)->get()->map(function (PdvOfflineSync $item) {
            return [
                'uuid_local' => $item->uuid_local,
                'status' => $item->status,
                'venda_caixa_id' => $item->venda_caixa_id,
                'sincronizado_em' => optional($item->sincronizado_em)->toDateTimeString(),
                'ultima_tentativa_em' => optional($item->ultima_tentativa_em)->toDateTimeString(),
                'tentativas' => (int) ($item->tentativas ?? 0),
                'erro' => $item->erro,
            ];
        })->values();

        $summaryQuery = clone $baseQuery;
        $rows = $summaryQuery->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');

        return [
            'empresa_id' => $empresaId,
            'resumo' => $this->summaryFromRows($rows),
            'itens' => $items,
            'consultado_em' => now()->toDateTimeString(),
        ];
    }

    public function dashboard(Request $request): array
    {
        $empresaId = TenantContext::empresaId($request);
        $baseQuery = PdvOfflineSync::query()->where('empresa_id', $empresaId);
        $rows = (clone $baseQuery)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
        $ultimosErros = (clone $baseQuery)
            ->whereIn('status', [self::STATUS_ERRO_RECUPERAVEL, self::STATUS_ERRO_FATAL, self::STATUS_CONFLITO_PAYLOAD])
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(fn (PdvOfflineSync $item) => [
                'uuid_local' => $item->uuid_local,
                'status' => $item->status,
                'erro' => $item->erro,
                'tentativas' => (int) ($item->tentativas ?? 0),
                'ultima_tentativa_em' => optional($item->ultima_tentativa_em)->toDateTimeString(),
            ])
            ->values();

        $ultimaSincronizada = (clone $baseQuery)
            ->whereIn('status', [self::STATUS_SINCRONIZADO, self::STATUS_DUPLICADO])
            ->orderByDesc('sincronizado_em')
            ->first();

        return [
            'empresa_id' => $empresaId,
            'metricas' => array_merge(
                $this->summaryFromRows($rows),
                ['ultima_sincronizacao_em' => optional($ultimaSincronizada?->sincronizado_em)->toDateTimeString()]
            ),
            'ultimos_erros' => $ultimosErros,
            'consultado_em' => now()->toDateTimeString(),
        ];
    }



    private function assertInfrastructureIsReady(): bool
    {
        if (!Schema::hasTable('pdv_offline_syncs')) {
            Log::error('PDV offline: tabela pdv_offline_syncs ausente.');
            return false;
        }

        foreach (['empresa_id', 'uuid_local', 'status'] as $column) {
            if (!Schema::hasColumn('pdv_offline_syncs', $column)) {
                Log::error('PDV offline: coluna obrigatória ausente.', ['coluna' => $column]);
                return false;
            }
        }

        return true;
    }

    private function syncOne(int $empresaId, int $usuarioId, array $payload): array
    {
        $payload = $this->normalizePayload($payload);
        $uuid = (string) data_get($payload, 'uuid_local', '');

        if ($uuid === '') {
            return $this->errorResult(self::STATUS_ERRO_FATAL, null, 'uuid_local é obrigatório.');
        }

        $validationError = $this->validatePayload($payload);
        if ($validationError !== null) {
            return $this->storeValidationFailure($empresaId, $usuarioId, $uuid, $payload, $validationError);
        }

        $hash = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        try {
            return DB::transaction(function () use ($empresaId, $usuarioId, $payload, $uuid, $hash) {
                $sync = PdvOfflineSync::query()
                    ->where('empresa_id', $empresaId)
                    ->where('uuid_local', $uuid)
                    ->lockForUpdate()
                    ->first();

                if ($sync && $sync->status === self::STATUS_SINCRONIZANDO) {
                    return [
                        'status' => self::STATUS_PENDENTE,
                        'uuid_local' => $uuid,
                        'mensagem' => 'Venda já está em sincronização.',
                        'tentativas' => (int) ($sync->tentativas ?? 0),
                    ];
                }

                if ($sync && $sync->payload_hash && $sync->payload_hash !== $hash) {
                    $updated = $this->persistSyncRecord($sync, [
                        'status' => self::STATUS_CONFLITO_PAYLOAD,
                        'request_payload' => $payload,
                        'ultima_tentativa_em' => now(),
                        'tentativas' => $this->nextAttemptCount($sync),
                        'erro' => 'Reenvio com uuid_local já existente e payload diferente.',
                    ]);

                    return [
                        'status' => self::STATUS_CONFLITO_PAYLOAD,
                        'uuid_local' => $uuid,
                        'tentativas' => (int) ($updated?->tentativas ?? $sync->tentativas ?? 1),
                        'mensagem' => 'UUID já utilizado com payload diferente.',
                    ];
                }

                if ($sync && $sync->venda_caixa_id) {
                    $duplicateTime = now();
                    $updated = $this->persistSyncRecord($sync, [
                        'status' => self::STATUS_DUPLICADO,
                        'response_payload' => [
                            'venda_caixa_id' => $sync->venda_caixa_id,
                            'status' => self::STATUS_DUPLICADO,
                        ],
                        'sincronizado_em' => $sync->sincronizado_em ?: $duplicateTime,
                        'erro' => null,
                        'ultima_tentativa_em' => $duplicateTime,
                        'tentativas' => $this->nextAttemptCount($sync),
                    ]);

                    return [
                        'status' => self::STATUS_DUPLICADO,
                        'uuid_local' => $uuid,
                        'venda_caixa_id' => $sync->venda_caixa_id,
                        'sincronizado_em' => optional($updated?->sincronizado_em ?? $sync->sincronizado_em)->toDateTimeString(),
                        'tentativas' => (int) ($updated?->tentativas ?? $sync->tentativas ?? 0),
                        'mensagem' => 'Venda já sincronizada anteriormente.',
                    ];
                }

                $startedAt = now();
                $sync = $this->persistSyncRecord($sync, [
                    'empresa_id' => $empresaId,
                    'usuario_id' => $usuarioId,
                    'uuid_local' => $uuid,
                    'payload_hash' => $hash,
                    'status' => self::STATUS_SINCRONIZANDO,
                    'request_payload' => $payload,
                    'erro' => null,
                    'ultima_tentativa_em' => $startedAt,
                    'tentativas' => $this->nextAttemptCount($sync),
                ]);

                $empresa = Empresa::findOrFail($empresaId);
                $naturezaId = optional(optional($empresa->configNota)->natureza)->id ?? optional($empresa->configNota)->nat_op_padrao;
                $stockMove = new StockMove();

                $itens = (array) data_get($payload, 'itens', []);
                $pagamentos = (array) data_get($payload, 'pagamentos', data_get($payload, 'fatura', []));

                $venda = VendaCaixa::create([
                    'cliente_id' => data_get($payload, 'cliente_id'),
                    'usuario_id' => data_get($payload, 'usuario_id', $usuarioId),
                    'valor_total' => $this->toFloat(data_get($payload, 'total', data_get($payload, 'valor_total', 0))),
                    'natureza_id' => $naturezaId,
                    'chave' => data_get($payload, 'chave', ''),
                    'estado_emissao' => 'novo',
                    'tipo_pagamento' => data_get($payload, 'pagamento_principal', data_get($payload, 'tipo_pagamento', '01')),
                    'forma_pagamento' => '',
                    'dinheiro_recebido' => $this->toFloat(data_get($payload, 'valor_recebido', data_get($payload, 'dinheiro_recebido', 0))),
                    'troco' => $this->toFloat(data_get($payload, 'troco', 0)),
                    'nome' => data_get($payload, 'cliente_nome', data_get($payload, 'nome', '')),
                    'cpf' => data_get($payload, 'cpf_cnpj', data_get($payload, 'cpf', '')),
                    'observacao' => trim('PDV OFFLINE UUID: ' . $uuid . ' | ' . (string) data_get($payload, 'observacao', '')),
                    'desconto' => $this->toFloat(data_get($payload, 'desconto', 0)),
                    'acrescimo' => $this->toFloat(data_get($payload, 'acrescimo', 0)),
                    'pedido_delivery_id' => 0,
                    'empresa_id' => $empresaId,
                    'bandeira_cartao' => data_get($payload, 'bandeira_cartao', ''),
                    'cnpj_cartao' => data_get($payload, 'cnpj_cartao', ''),
                    'cAut_cartao' => data_get($payload, 'caut_cartao', data_get($payload, 'cAut_cartao', '')),
                    'descricao_pag_outros' => '',
                    'rascunho' => 0,
                    'consignado' => 0,
                    'pdv_java' => 0,
                    'filial_id' => data_get($payload, 'filial_id'),
                    'created_at' => data_get($payload, 'criado_em', $startedAt->toDateTimeString()),
                    'updated_at' => $startedAt,
                ]);

                foreach ($itens as $item) {
                    $produto = Produto::findOrFail((int) data_get($item, 'produto_id'));
                    $cfop = optional(optional($empresa->configNota)->natureza)->sobrescreve_cfop
                        ? optional(optional($empresa->configNota)->natureza)->CFOP_saida_estadual
                        : $produto->CFOP_saida_estadual;

                    $quantidade = $this->toFloat(data_get($item, 'quantidade', 0));
                    $valor = $this->toFloat(data_get($item, 'valor', data_get($item, 'valor_unitario', 0)));

                    ItemVendaCaixa::create([
                        'produto_id' => $produto->id,
                        'venda_caixa_id' => $venda->id,
                        'quantidade' => $quantidade,
                        'valor' => $valor,
                        'item_pedido_id' => null,
                        'observacao' => data_get($item, 'observacao', ''),
                        'cfop' => $cfop,
                        'valor_custo' => $produto->valor_compra,
                    ]);

                    $stockMove->downStock($produto->id, $quantidade, data_get($payload, 'filial_id'));
                }

                foreach ($pagamentos as $pagamento) {
                    FaturaFrenteCaixa::create([
                        'valor' => $this->toFloat(data_get($pagamento, 'valor', 0)),
                        'forma_pagamento' => (string) data_get($pagamento, 'forma_pagamento', data_get($pagamento, 'tipo', data_get($payload, 'pagamento_principal', '01'))),
                        'venda_caixa_id' => $venda->id,
                    ]);
                }

                $finishedAt = now();
                $updated = $this->persistSyncRecord($sync, [
                    'empresa_id' => $empresaId,
                    'usuario_id' => $usuarioId,
                    'uuid_local' => $uuid,
                    'payload_hash' => $hash,
                    'status' => self::STATUS_SINCRONIZADO,
                    'venda_caixa_id' => $venda->id,
                    'response_payload' => [
                        'venda_caixa_id' => $venda->id,
                        'status' => self::STATUS_SINCRONIZADO,
                    ],
                    'sincronizado_em' => $finishedAt,
                    'erro' => null,
                    'ultima_tentativa_em' => $finishedAt,
                ]);

                $this->logSync('info', 'PDV offline sincronizado', [
                    'empresa_id' => $empresaId,
                    'uuid_local' => $uuid,
                    'venda_caixa_id' => $venda->id,
                ]);

                return [
                    'status' => self::STATUS_SINCRONIZADO,
                    'uuid_local' => $uuid,
                    'venda_caixa_id' => $venda->id,
                    'numero_nfce' => $venda->numero_nfce ?? 0,
                    'sincronizado_em' => $finishedAt->toDateTimeString(),
                    'tentativas' => (int) ($updated?->tentativas ?? 1),
                ];
            });
        } catch (\Throwable $e) {
            $sync = PdvOfflineSync::where('empresa_id', $empresaId)
                ->where('uuid_local', $uuid)
                ->first();

            $status = $this->classifyErrorStatus($e, $sync);
            $updatedSync = $this->persistSyncError($sync, [
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'uuid_local' => $uuid,
                'payload_hash' => $hash,
                'status' => $status,
                'request_payload' => $payload,
                'erro' => mb_substr($e->getMessage(), 0, 1000),
                'ultima_tentativa_em' => now(),
                'tentativas' => $this->nextAttemptCount($sync),
            ]);

            $this->logSync('error', 'Falha ao sincronizar PDV offline', [
                'empresa_id' => $empresaId,
                'uuid_local' => $uuid,
                'status' => $status,
                'mensagem' => $e->getMessage(),
            ]);

            return [
                'status' => $status,
                'uuid_local' => $uuid,
                'tentativas' => (int) ($updatedSync?->tentativas ?? $sync?->tentativas ?? 1),
                'pode_tentar_novamente' => $status === self::STATUS_ERRO_RECUPERAVEL
                    && (int) ($updatedSync?->tentativas ?? $sync?->tentativas ?? 1) < self::MAX_TENTATIVAS_PADRAO,
                'mensagem' => $e->getMessage(),
            ];
        }
    }

    private function invalidBatchResponse(string $message, int $empresaId = 0): array
    {
        return [
            'empresa_id' => $empresaId,
            'sincronizadas' => 0,
            'duplicadas' => 0,
            'conflitos' => 0,
            'com_erro' => 1,
            'vendas' => [[
                'status' => self::STATUS_ERRO_FATAL,
                'uuid_local' => null,
                'mensagem' => $message,
            ]],
            'processado_em' => now()->toDateTimeString(),
        ];
    }

    private function extractSalesPayload(Request $request): array
    {
        $vendas = $request->input('vendas', []);

        if (!is_array($vendas) || empty($vendas)) {
            $single = $request->all();
            if (isset($single['uuid_local'])) {
                $vendas = [$single];
            }
        }

        return is_array($vendas) ? array_values($vendas) : [];
    }

    private function normalizePayload(array $payload): array
    {
        if (!isset($payload['pagamentos']) && isset($payload['fatura']) && is_array($payload['fatura'])) {
            $payload['pagamentos'] = $payload['fatura'];
        }

        $payload['itens'] = array_values(array_filter((array) data_get($payload, 'itens', []), function ($item) {
            return is_array($item) && (int) data_get($item, 'produto_id', 0) > 0;
        }));

        $payload['pagamentos'] = array_values(array_filter((array) data_get($payload, 'pagamentos', []), function ($pagamento) {
            return is_array($pagamento);
        }));

        ksort($payload);

        return $payload;
    }

    private function validatePayload(array $payload): ?string
    {
        if (empty($payload['itens'])) {
            return 'A venda offline precisa ter ao menos um item.';
        }

        foreach ((array) $payload['itens'] as $index => $item) {
            if ((int) data_get($item, 'produto_id', 0) <= 0) {
                return 'Item ' . ($index + 1) . ' sem produto_id válido.';
            }

            if ($this->toFloat(data_get($item, 'quantidade', 0)) <= 0) {
                return 'Item ' . ($index + 1) . ' com quantidade inválida.';
            }
        }

        return null;
    }

    private function storeValidationFailure(int $empresaId, int $usuarioId, string $uuid, array $payload, string $message): array
    {
        $hash = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $sync = PdvOfflineSync::where('empresa_id', $empresaId)
            ->where('uuid_local', $uuid)
            ->first();

        $updatedSync = $this->persistSyncError($sync, [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'uuid_local' => $uuid,
            'payload_hash' => $hash,
            'status' => self::STATUS_ERRO_FATAL,
            'request_payload' => $payload,
            'erro' => $message,
            'ultima_tentativa_em' => now(),
            'tentativas' => $this->nextAttemptCount($sync),
        ]);

        return [
            'status' => self::STATUS_ERRO_FATAL,
            'uuid_local' => $uuid,
            'tentativas' => (int) ($updatedSync?->tentativas ?? $sync?->tentativas ?? 1),
            'mensagem' => $message,
        ];
    }

    private function nextAttemptCount(?PdvOfflineSync $sync): int
    {
        return ((int) ($sync?->tentativas ?? 0)) + 1;
    }

    private function classifyErrorStatus(\Throwable $e, ?PdvOfflineSync $sync): string
    {
        $attempts = $this->nextAttemptCount($sync);
        $message = mb_strtolower($e->getMessage());

        if (str_contains($message, 'deadlock') || str_contains($message, 'lock wait') || str_contains($message, 'timeout')) {
            return self::STATUS_ERRO_RECUPERAVEL;
        }

        if ($attempts >= self::MAX_TENTATIVAS_PADRAO) {
            return self::STATUS_ERRO_FATAL;
        }

        return self::STATUS_ERRO_RECUPERAVEL;
    }

    private function persistSyncRecord(?PdvOfflineSync $sync, array $attributes): ?PdvOfflineSync
    {
        if (!Schema::hasTable('pdv_offline_syncs')) {
            return $sync;
        }

        $allowedColumns = array_flip(Schema::getColumnListing('pdv_offline_syncs'));
        $safeAttributes = [];

        foreach ($attributes as $key => $value) {
            if (!isset($allowedColumns[$key])) {
                continue;
            }

            if (in_array($key, ['request_payload', 'response_payload'], true) && is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $safeAttributes[$key] = $value;
        }

        if (empty($safeAttributes)) {
            return $sync;
        }

        $syncModel = $sync ?: new PdvOfflineSync();
        $syncModel->fill($safeAttributes);
        $syncModel->save();

        return $syncModel->fresh();
    }

    private function persistSyncError(?PdvOfflineSync $sync, array $attributes): ?PdvOfflineSync
    {
        try {
            return $this->persistSyncRecord($sync, $attributes);
        } catch (\Throwable $syncPersistError) {
            report($syncPersistError);

            return $sync;
        }
    }

    private function summaryFromRows($rows): array
    {
        return [
            self::STATUS_PENDENTE => (int) ($rows[self::STATUS_PENDENTE] ?? 0),
            self::STATUS_SINCRONIZANDO => (int) ($rows[self::STATUS_SINCRONIZANDO] ?? 0),
            self::STATUS_SINCRONIZADO => (int) ($rows[self::STATUS_SINCRONIZADO] ?? 0),
            self::STATUS_DUPLICADO => (int) ($rows[self::STATUS_DUPLICADO] ?? 0),
            self::STATUS_CONFLITO_PAYLOAD => (int) ($rows[self::STATUS_CONFLITO_PAYLOAD] ?? 0),
            self::STATUS_ERRO_RECUPERAVEL => (int) ($rows[self::STATUS_ERRO_RECUPERAVEL] ?? 0),
            self::STATUS_ERRO_FATAL => (int) ($rows[self::STATUS_ERRO_FATAL] ?? 0),
        ];
    }

    private function errorResult(string $status, ?string $uuid, string $message): array
    {
        return [
            'status' => $status,
            'uuid_local' => $uuid,
            'mensagem' => $message,
        ];
    }

    private function logSync(string $level, string $message, array $context = []): void
    {
        Log::channel(config('logging.default'))->{$level}($message, $context);
    }

    private function toFloat($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace('.', '', (string) $value);
        $normalized = str_replace(',', '.', $normalized);

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }
}
