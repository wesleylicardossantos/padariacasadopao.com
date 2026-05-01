<?php

namespace App\Console\Commands;

use App\Modules\Estoque\Services\StockGovernanceReportService;
use Illuminate\Console\Command;

class StockWriteGuardReportCommand extends Command
{
    protected $signature = 'stock:write-guard-report {--write : Persiste o relatório em docs/operacao}';

    protected $description = 'Gera relatório de hotspots e violações de escrita direta fora do ledger.';

    public function handle(StockGovernanceReportService $service): int
    {
        $report = $service->generate((bool) $this->option('write'));

        $this->info('Monitoramento ativo: ' . ($report['monitoring_enabled'] ? 'sim' : 'não'));
        $this->info('Bloqueio ativo: ' . ($report['blocking_enabled'] ? 'sim' : 'não'));
        $this->info('Hotspots encontrados: ' . count($report['hotspots']));
        $this->info('Violações recentes: ' . $report['recent_violation_count']);

        if (!empty($report['hotspots'])) {
            $this->table(['file', 'pattern'], array_slice($report['hotspots'], 0, 20));
        }

        return self::SUCCESS;
    }
}
