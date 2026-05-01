<?php

namespace App\Console\Commands;

use App\Services\RHFolhaCompetenciaService;
use Illuminate\Console\Command;

class RHFolhaProcessarCompetenciaCommand extends Command
{
    protected $signature = 'rh:folha-processar {empresa_id} {mes} {ano} {--sobrescrever} {--integrar-financeiro} {--vencimento=} {--filial_id=}';
    protected $description = 'Processa a competência da folha com eventos automáticos, bases legais e itens detalhados.';

    public function __construct(private RHFolhaCompetenciaService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $gerados = $this->service->processar(
            (int) $this->argument('empresa_id'),
            (int) $this->argument('mes'),
            (int) $this->argument('ano'),
            (bool) $this->option('sobrescrever'),
            (bool) $this->option('integrar-financeiro'),
            $this->option('vencimento') ?: null,
            $this->option('filial_id') !== null ? (int) $this->option('filial_id') : null,
        );

        $this->info('Competência processada com sucesso. Apurações geradas: ' . $gerados);
        return self::SUCCESS;
    }
}
