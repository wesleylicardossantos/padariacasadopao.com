<?php

namespace App\Console\Commands;

use App\Services\RH\RHDossieAutomationService;
use Illuminate\Console\Command;

class SyncRHDossieAutomationCommand extends Command
{
    protected $signature = 'rh:dossie-sync {empresa_id? : Empresa alvo para sync}';
    protected $description = 'Sincroniza os eventos automáticos e indicadores do dossiê RH';

    public function handle(RHDossieAutomationService $service): int
    {
        $empresaId = (int) ($this->argument('empresa_id') ?: 0);
        $stats = $service->syncEmpresa($empresaId > 0 ? $empresaId : null);

        $this->info(sprintf(
            'Dossiê sincronizado. Funcionários: %d | Criados: %d | Atualizados: %d | Sem alterações: %d',
            $stats['funcionarios'] ?? 0,
            $stats['created'] ?? 0,
            $stats['updated'] ?? 0,
            $stats['skipped'] ?? 0,
        ));

        return self::SUCCESS;
    }
}
