<?php

namespace App\Helpers;

use App\Models\Empresa;
use App\Models\Estoque;
use App\Models\Produto;
use App\Modules\Estoque\DTOs\StockMovementData;
use App\Modules\Estoque\Services\StockLedgerService;

class StockMove
{
    private function normalizeFilialId($filial_id): ?int
    {
        if ($filial_id === null || $filial_id === '' || (int) $filial_id === -1 || (int) $filial_id === 0) {
            return null;
        }

        return (int) $filial_id;
    }

    private function existStock($productId, $filial_id)
    {
        return Estoque::where('produto_id', $productId)
            ->when($filial_id !== null, function ($query) use ($filial_id) {
                return $query->where('filial_id', $filial_id);
            }, function ($query) {
                return $query->whereNull('filial_id');
            })
            ->first();
    }

    private function ledger(): StockLedgerService
    {
        return app(StockLedgerService::class);
    }

    public function getStockProduct($productId, $filial_id)
    {
        $produto = Produto::find($productId);
        $empresaId = $produto?->empresa_id ?: Empresa::getId();

        return $this->ledger()->currentBalance((int) $empresaId, (int) $productId, $this->normalizeFilialId($filial_id));
    }

    public function pluStock($productId, $quantity, $value = -1, $filial_id = null)
    {
        $produto = Produto::findOrFail($productId);
        $data = new StockMovementData(
            empresaId: (int) ($produto->empresa_id ?: Empresa::getId()),
            filialId: $this->normalizeFilialId($filial_id),
            productId: (int) $productId,
            quantity: (float) $quantity,
            unitCost: $value > -1 ? (float) $value : (float) ($produto->valor_compra ?? 0),
            source: 'legacy_helper_entry',
            sourceId: null,
            notes: 'Entrada registrada pelo helper legado StockMove.',
            metadata: ['bridge' => 'legacy_stockmove'],
            performedBy: function_exists('get_id_user') ? (int) get_id_user() : null,
            occurredAt: now()->toDateTimeString(),
        );

        $this->ledger()->entry($data);

        return true;
    }

    public function downStock($productId, $quantity, $filial_id = null)
    {
        $produto = Produto::findOrFail($productId);
        $data = new StockMovementData(
            empresaId: (int) ($produto->empresa_id ?: Empresa::getId()),
            filialId: $this->normalizeFilialId($filial_id),
            productId: (int) $productId,
            quantity: (float) $quantity,
            unitCost: null,
            source: 'legacy_helper_exit',
            sourceId: null,
            notes: 'Saída registrada pelo helper legado StockMove.',
            metadata: ['bridge' => 'legacy_stockmove'],
            performedBy: function_exists('get_id_user') ? (int) get_id_user() : null,
            occurredAt: now()->toDateTimeString(),
        );

        $this->ledger()->exit($data);

        return true;
    }
}
