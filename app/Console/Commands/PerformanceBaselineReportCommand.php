<?php

namespace App\Console\Commands;

use App\Support\Cutoff\PerformanceBaselineInspector;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PerformanceBaselineReportCommand extends Command
{
    protected $signature = 'performance:baseline-report {--write : Grava o relatório em docs/operacao}';

    protected $description = 'Gera baseline de performance e hotspots de query/superfície de rotas.';

    public function handle(PerformanceBaselineInspector $inspector, Filesystem $files): int
    {
        $report = $inspector->build();

        $this->info('Config cached: ' . ($report['cache']['config_cached'] ? 'sim' : 'não'));
        $this->info('Routes cached: ' . ($report['cache']['routes_cached'] ? 'sim' : 'não'));
        $this->line('Queue connection: ' . $report['queue']['connection']);
        $this->line('Critical tables reviewed: ' . count($report['database']['critical_tables']));
        $this->line('Query hotspots encontrados: ' . count($report['query_hotspots']));

        if ($this->option('write')) {
            $files->ensureDirectoryExists(base_path('docs/operacao'));
            $files->put(base_path('docs/operacao/performance_baseline_report.json'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $files->put(base_path('docs/operacao/performance_baseline_report.md'), $this->toMarkdown($report));
            $this->info('Relatórios gravados em docs/operacao/performance_baseline_report.json e .md');
        }

        return self::SUCCESS;
    }

    private function toMarkdown(array $report): string
    {
        $md = "# Performance Baseline Report\n\n";
        $md .= '- generated_at: ' . $report['generated_at'] . "\n";
        $md .= '- config_cached: ' . ($report['cache']['config_cached'] ? 'true' : 'false') . "\n";
        $md .= '- routes_cached: ' . ($report['cache']['routes_cached'] ? 'true' : 'false') . "\n";
        $md .= '- queue_connection: ' . $report['queue']['connection'] . "\n\n";
        $md .= "## Recommendations\n";
        foreach ($report['recommendations'] as $item) {
            $md .= '- ' . $item . "\n";
        }

        return $md;
    }
}
