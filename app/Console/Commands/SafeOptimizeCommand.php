<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SafeOptimizeCommand extends Command
{
    protected $signature = 'system:safe-optimize';
    protected $description = 'Limpa e recompõe caches de forma segura para hospedagem compartilhada.';

    public function handle(): int
    {
        foreach (['config:clear', 'route:clear', 'view:clear', 'cache:clear'] as $command) {
            Artisan::call($command);
            $this->line(trim(Artisan::output()));
        }

        foreach (['config:cache', 'route:cache', 'view:cache'] as $command) {
            try {
                Artisan::call($command);
                $this->line(trim(Artisan::output()));
            } catch (\Throwable $e) {
                $this->warn($command . ' falhou: ' . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
