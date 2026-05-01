<?php

namespace App\Modules\Estoque\Services;

use App\Models\Estoque;
use App\Models\Produto;
use App\Modules\Estoque\DTOs\StockMovementData;
use App\Modules\Estoque\Repositories\StockMovementRepository;
use App\Modules\Estoque\Support\LegacyStockWriteGuard;
use DomainException;
use Illuminate\Support\Facades\DB;

class StockLedgerService
{
    public function __construct(
        private readonly StockMovementRepository $repository,
        private readonly LegacyStockWriteGuard $legacyStockWriteGuard,
    ) {
    }

    public function entry(StockMovementData $data): array
    {
        return $this->record('in', $data);
    }

    public function exit(StockMovementData $data): array
    {
        return $this->record('out', $data);
    }

    public function adjustment(StockMovementData $data): array
    {
        return $this->record('adjustment', $data);
    }

    public function currentBalance(int $empresaId, int $productId, ?int $filialId = null): float
    {
        $this->bootstrapOpeningBalance($empresaId, $productId, $filialId);

        return $this->repository->latestBalance($empresaId, $productId, $filialId);
    }

    public function currentUnitCost(int $empresaId, int $productId, ?int $filialId = null): ?float
    {
        $this->bootstrapOpeningBalance($empresaId, $productId, $filialId);

        return $this->repository->latestCost($empresaId, $productId, $filialId);
    }

    public function rebuildLegacyBalance(int $empresaId, int $productId, ?int $filialId = null, ?float $unitCost = null): void
    {
        $balance = $this->repository->latestBalance($empresaId, $productId, $filialId);
        $produto = Produto::find($productId);

        $this->legacyStockWriteGuard->runWithinGuard(function () use ($empresaId, $productId, $filialId, $balance, $unitCost, $produto) {
            Estoque::query()->updateOrCreate(
                [
                    'empresa_id' => $empresaId,
                    'produto_id' => $productId,
                    'filial_id' => $filialId,
                ],
                [
                    'quantidade' => $balance,
                    'valor_compra' => $unitCost ?? $this->repository->latestCost($empresaId, $productId, $filialId) ?? ($produto?->valor_compra ?? 0),
                    'validade' => null,
                ]
            );
        }, 'stock_ledger_projection');
    }

    public function snapshotByProduct(int $empresaId, int $limit = 100): array
    {
        $items = Estoque::query()
            ->with('produto:id,nome,referencia,estoque_minimo,gerenciar_estoque')
            ->where('empresa_id', $empresaId)
            ->orderBy('produto_id')
            ->limit($limit)
            ->get();

        return $items->map(function (Estoque $estoque) {
            return [
                'produto_id' => $estoque->produto_id,
                'produto' => $estoque->produto?->nome,
                'referencia' => $estoque->produto?->referencia,
                'filial_id' => $estoque->filial_id,
                'saldo' => (float) $estoque->quantidade,
                'estoque_minimo' => (float) ($estoque->produto?->estoque_minimo ?? 0),
                'abaixo_minimo' => (float) $estoque->quantidade <= (float) ($estoque->produto?->estoque_minimo ?? 0),
            ];
        })->all();
    }

    private function record(string $type, StockMovementData $data): array
    {
        return DB::transaction(function () use ($type, $data) {
            $this->bootstrapOpeningBalance($data->empresaId, $data->productId, $data->filialId);

            $previousBalance = $this->repository->latestBalance($data->empresaId, $data->productId, $data->filialId);
            $quantity = abs($data->quantity);
            $delta = $type === 'out' ? -$quantity : $quantity;

            if ($type === 'adjustment') {
                $delta = $data->quantity;
            }

            $product = Produto::find($data->productId);
            $shouldManage = (bool) ($product?->gerenciar_estoque ?? true);
            $newBalance = round($previousBalance + $delta, 4);

            if ($shouldManage && $newBalance < -0.0001) {
                throw new DomainException('Estoque insuficiente para concluir a operação.');
            }

            $movement = $this->repository->create([
                'empresa_id' => $data->empresaId,
                'filial_id' => $data->filialId,
                'product_id' => $data->productId,
                'type' => $type,
                'quantity' => $quantity,
                'balance_after' => max(0, $newBalance),
                'unit_cost' => $data->unitCost ?? $this->repository->latestCost($data->empresaId, $data->productId, $data->filialId) ?? ($product?->valor_compra ?? null),
                'source' => $data->source,
                'source_id' => $data->sourceId,
                'notes' => $data->notes,
                'metadata' => $data->metadata,
                'performed_by' => $data->performedBy,
                'occurred_at' => $data->occurredAt ?? now(),
            ]);

            $this->rebuildLegacyBalance($data->empresaId, $data->productId, $data->filialId, $movement->unit_cost);

            return [
                'movement' => $movement,
                'previous_balance' => $previousBalance,
                'current_balance' => $movement->balance_after,
            ];
        });
    }

    private function bootstrapOpeningBalance(int $empresaId, int $productId, ?int $filialId = null): void
    {
        $hasMovements = $this->repository->query($empresaId, $productId, $filialId)->exists();
        if ($hasMovements) {
            return;
        }

        $legacyStock = Estoque::query()
            ->where('empresa_id', $empresaId)
            ->where('produto_id', $productId)
            ->when($filialId !== null, fn ($query) => $query->where('filial_id', $filialId), fn ($query) => $query->whereNull('filial_id'))
            ->first();

        if (!$legacyStock || (float) $legacyStock->quantidade <= 0) {
            return;
        }

        $this->repository->create([
            'empresa_id' => $empresaId,
            'filial_id' => $filialId,
            'product_id' => $productId,
            'type' => 'opening_balance',
            'quantity' => (float) $legacyStock->quantidade,
            'balance_after' => (float) $legacyStock->quantidade,
            'unit_cost' => (float) ($legacyStock->valor_compra ?? 0),
            'source' => 'legacy_stock',
            'source_id' => $legacyStock->id,
            'notes' => 'Saldo inicial importado automaticamente da tabela estoque.',
            'metadata' => ['bootstrap' => true],
            'performed_by' => null,
            'occurred_at' => now(),
        ]);
    }
}
