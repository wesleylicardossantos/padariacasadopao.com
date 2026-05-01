<?php

namespace App\Modules\Estoque\Services;

use App\Models\Estoque;
use App\Models\Produto;

class InventorySnapshotService
{
    public function __construct(private readonly StockLedgerService $ledger)
    {
    }

    public function summary(int $empresaId): array
    {
        $totals = Estoque::query()
            ->where('empresa_id', $empresaId)
            ->selectRaw('COUNT(*) as registros, COALESCE(SUM(quantidade),0) as saldo_total, COALESCE(SUM(CASE WHEN quantidade <= 0 THEN 1 ELSE 0 END),0) as zerados')
            ->first();

        $belowMinimum = Produto::query()
            ->where('empresa_id', $empresaId)
            ->where('gerenciar_estoque', 1)
            ->whereHas('estoque', function ($query) {
                $query->whereColumn('estoques.quantidade', '<=', 'produtos.estoque_minimo');
            })
            ->count();

        return [
            'registros' => (int) ($totals->registros ?? 0),
            'saldo_total' => (float) ($totals->saldo_total ?? 0),
            'produtos_zerados' => (int) ($totals->zerados ?? 0),
            'abaixo_minimo' => $belowMinimum,
            'ultimas_movimentacoes' => $this->ledger->snapshotByProduct($empresaId, 20),
        ];
    }
}
