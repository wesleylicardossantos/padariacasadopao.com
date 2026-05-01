<?php

namespace App\Console\Commands;

use App\Modules\SaaS\Services\UsageSnapshotService;
use Illuminate\Console\Command;

class SaasSnapshotUsageCommand extends Command
{
    protected $signature = 'saas:snapshot-usage {empresa_id}';
    protected $description = 'Gera um snapshot de uso dos limites SaaS para uma empresa.';

    public function handle(UsageSnapshotService $service): int
    {
        $snapshot = $service->snapshot((int) $this->argument('empresa_id'));

        if (! $snapshot) {
            $this->warn('Tabela de snapshots ainda não existe. Execute as migrations.');
            return self::FAILURE;
        }

        $this->info('Snapshot gerado com sucesso: #'.$snapshot->id);
        return self::SUCCESS;
    }
}
