<?php

namespace App\Console\Commands;

use App\Models\Estoque;
use App\Modules\Estoque\Services\StockLedgerService;
use Illuminate\Console\Command;

class StockRebuildLedgerCommand extends Command
{
    protected $signature = 'stock:rebuild-ledger {empresa_id?}';

    protected $description = 'Inicializa o razão de estoque a partir da tabela estoque legada.';

    public function handle(StockLedgerService $ledger): int
    {
        $empresaId = $this->argument('empresa_id');

        $query = Estoque::query()->orderBy('id');
        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        $count = 0;
        foreach ($query->cursor() as $estoque) {
            $ledger->currentBalance((int) $estoque->empresa_id, (int) $estoque->produto_id, $estoque->filial_id ? (int) $estoque->filial_id : null);
            $count++;
        }

        $this->info("Razão de estoque sincronizado para {$count} registros legados.");

        return self::SUCCESS;
    }
}
