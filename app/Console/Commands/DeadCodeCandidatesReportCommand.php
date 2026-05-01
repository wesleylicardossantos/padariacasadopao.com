<?php

namespace App\Console\Commands;

use App\Support\Observability\HardeningInspector;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class DeadCodeCandidatesReportCommand extends Command
{
    protected $signature = 'deadcode:candidates-report {--write : Grava o relatório em docs/operacao}';

    protected $description = 'Lista candidatos a código temporário/duplicado para corte gradual e seguro.';

    public function handle(HardeningInspector $inspector, Filesystem $files): int
    {
        $report = $inspector->build()['dead_code_candidates'];

        $this->info('Candidatos a código morto/temporário: ' . ($report['count'] ?? 0));
        foreach (array_slice($report['candidates'] ?? [], 0, 20) as $candidate) {
            $this->line('- ' . ($candidate['path'] ?? '-') . ' :: ' . ($candidate['reason'] ?? ''));
        }

        if ($this->option('write')) {
            $dir = base_path('docs/operacao');
            $files->ensureDirectoryExists($dir);
            $files->put($dir . '/dead_code_candidates_report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->info('Relatório gravado em docs/operacao');
        }

        return self::SUCCESS;
    }
}
