<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Modules\SaaS\Services\TenantScalabilityService;
use App\Modules\SaaS\Services\UsageSnapshotService;
use Illuminate\Console\Command;

class SaasScaleSnapshotCommand extends Command
{
    protected $signature = 'saas:scale-snapshot {--limit=100}';
    protected $description = 'Gera snapshots de uso e relatório de prontidão para escala SaaS.';

    public function handle(UsageSnapshotService $snapshots, TenantScalabilityService $scale): int
    {
        $limit = (int) $this->option('limit');
        $empresas = Empresa::query()->limit($limit)->pluck('id');

        foreach ($empresas as $empresaId) {
            $snapshots->snapshot((int) $empresaId);
            $payload = $scale->readiness((int) $empresaId);
            $this->line("Empresa {$empresaId}: score {$payload['score']}");
        }

        return self::SUCCESS;
    }
}
