<?php

namespace App\Console\Commands;

use App\Modules\PDV\Services\PdvReprocessService;
use Illuminate\Console\Command;

class PdvOfflineRetryCommand extends Command
{
    protected $signature = 'pdv:offline-retry {empresa_id : ID da empresa} {--limit=100 : Quantidade máxima de registros}';
    protected $description = 'Prepara registros do PDV offline com erro/pendência para nova tentativa de sincronização.';

    public function handle(PdvReprocessService $service): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        $limit = (int) $this->option('limit');
        $updated = $service->markForRetry($empresaId, $limit);

        $this->info("Registros preparados para reprocessamento: {$updated}");

        return self::SUCCESS;
    }
}
