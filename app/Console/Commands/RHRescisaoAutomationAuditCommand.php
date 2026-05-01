<?php

namespace App\Console\Commands;

use App\Models\RHRescisao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class RHRescisaoAutomationAuditCommand extends Command
{
    protected $signature = 'rh:rescisao-audit';
    protected $description = 'Audita rescisões sem itens ou com total líquido negativo.';

    public function handle(): int
    {
        if (!Schema::hasTable('rh_rescisoes')) {
            $this->warn('Tabela rh_rescisoes não encontrada.');
            return self::SUCCESS;
        }

        $query = RHRescisao::query()->withCount('itens');
        $inconsistentes = (clone $query)->where(function ($q) {
            $q->where('total_liquido', '<', 0)->orWhere('status', '!=', 'processada');
        })->count();

        $semItens = Schema::hasTable('rh_rescisao_itens')
            ? (clone $query)->having('itens_count', '=', 0)->count()
            : 0;

        $this->info('Rescisões inconsistentes: ' . $inconsistentes);
        $this->info('Rescisões sem itens: ' . $semItens);

        return self::SUCCESS;
    }
}
