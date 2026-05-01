<?php

namespace App\Console\Commands;

use App\Modules\Financeiro\Services\FinancialAuditService;
use Illuminate\Console\Command;

class FinancialAuditCommand extends Command
{
    protected $signature = 'financeiro:audit {empresa_id} {--filial=todos} {--json}';
    protected $description = 'Valida inconsistências financeiras e exibe um resumo executivo.';

    public function handle(FinancialAuditService $audit): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        $filialId = $this->option('filial');
        $result = $audit->validate($empresaId, $filialId);

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return self::SUCCESS;
        }

        $this->info('Saúde financeira: '.$result['resumo']['saude_financeira']);
        $this->table(['Código', 'Título', 'Qtd', 'Impacto', 'Severidade'], collect($result['checks'])->map(fn ($check) => [
            $check['code'],
            $check['title'],
            $check['count'],
            number_format($check['impact_value'], 2, ',', '.'),
            $check['status_label'],
        ])->all());

        return self::SUCCESS;
    }
}
