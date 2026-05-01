<?php

namespace App\Console\Commands;

use App\Modules\AI\Services\BusinessInsightService;
use Illuminate\Console\Command;

class GenerateBusinessInsightsCommand extends Command
{
    protected $signature = 'ai:generate-insights {empresa_id}';
    protected $description = 'Gera previsões e insights operacionais para a empresa informada.';

    public function handle(BusinessInsightService $service): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        $payload = $service->overview($empresaId);
        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return self::SUCCESS;
    }
}
