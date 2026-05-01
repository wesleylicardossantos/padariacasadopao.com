<?php

namespace App\Console\Commands;

use App\Services\OfficialLaborReferenceService;
use Illuminate\Console\Command;

class SyncOfficialLaborDataCommand extends Command
{
    protected $signature = 'rh:sync-official-labor-data {--force : Recarrega toda a base oficial local}';

    protected $description = 'Sincroniza tabelas oficiais de categoria do trabalhador, tipo de contrato, natureza da atividade e CBO.';

    public function handle(OfficialLaborReferenceService $service): int
    {
        $this->info('Sincronizando bases oficiais...');

        try {
            $result = $service->syncAll((bool) $this->option('force'));
        } catch (\Throwable $e) {
            $this->error('Falha na sincronização: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->table(['Base', 'Registros'], [
            ['Categorias do trabalhador', $result['categorias'] ?? 0],
            ['Tipos de contrato', $result['tipos_contrato'] ?? 0],
            ['Naturezas da atividade', $result['naturezas'] ?? 0],
            ['Departamentos', $result['departamentos'] ?? 0],
            ['CBO', $result['cbo'] ?? 0],
        ]);

        $this->info('Sincronização concluída.');

        return self::SUCCESS;
    }
}
