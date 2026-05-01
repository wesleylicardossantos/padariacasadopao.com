<?php

namespace App\Modules\Estoque\DTOs;

use Illuminate\Http\Request;

class StockMovementData
{
    public function __construct(
        public readonly int $empresaId,
        public readonly ?int $filialId,
        public readonly int $productId,
        public readonly float $quantity,
        public readonly ?float $unitCost = null,
        public readonly ?string $source = null,
        public readonly ?int $sourceId = null,
        public readonly ?string $notes = null,
        public readonly array $metadata = [],
        public readonly ?int $performedBy = null,
        public readonly ?string $occurredAt = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            empresaId: (int) $request->empresa_id,
            filialId: $request->filled('filial_id') && (int) $request->filial_id > 0 ? (int) $request->filial_id : null,
            productId: (int) $request->product_id,
            quantity: (float) str_replace(',', '.', (string) $request->quantity),
            unitCost: $request->filled('unit_cost') ? (float) str_replace(',', '.', (string) $request->unit_cost) : null,
            source: $request->input('source'),
            sourceId: $request->filled('source_id') ? (int) $request->source_id : null,
            notes: $request->input('notes'),
            metadata: (array) $request->input('metadata', []),
            performedBy: function_exists('get_id_user') ? (int) get_id_user() : null,
            occurredAt: $request->input('occurred_at'),
        );
    }

    public function toArray(): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'filial_id' => $this->filialId,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unitCost,
            'source' => $this->source,
            'source_id' => $this->sourceId,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'performed_by' => $this->performedBy,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
