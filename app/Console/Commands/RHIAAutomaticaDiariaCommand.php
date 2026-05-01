<?php

namespace App\Console\Commands;

use App\Services\RHMaximoAutomationService;
use Illuminate\Console\Command;

class RHIAAutomaticaDiariaCommand extends Command
{
    protected $signature = 'rh:ia-automatica-diaria';
    protected $description = 'Executa a IA automática do RH para empresas ativas';

    public function handle()
    {
        $resultado = RHMaximoAutomationService::processarTodasEmpresas();
        $this->info('IA automática executada para ' . count($resultado) . ' empresa(s).');
        return 0;
    }
}
