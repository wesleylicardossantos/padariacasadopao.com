<?php

namespace App\Console\Commands;

use App\Support\Cutoff\LegacyCutoffReadinessInspector;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class LegacyCutoffReadinessReportCommand extends Command
{
    protected $signature = 'legacy:cutoff-readiness-report {--write : Grava o relatório em docs/operacao}';

    protected $description = 'Avalia se já é seguro ativar bloqueios mais duros e cortar caminhos legados observados.';

    public function handle(LegacyCutoffReadinessInspector $inspector, Filesystem $files): int
    {
        $report = $inspector->build();

        $this->info('Pronto para cutoff seguro: ' . ($report['overall']['ready_for_safe_cutoff'] ? 'sim' : 'não'));
        $this->line('Violações recentes de estoque: ' . $report['stock_write_block']['violation_count']);
        $this->line('Artefatos presentes: ' . collect($report['reports']['items'])->where('exists', true)->count() . '/' . count($report['reports']['items']));
        $this->line('Surface findings: ' . count($report['public_surface']['findings']));

        if ($this->option('write')) {
            $files->ensureDirectoryExists(base_path('docs/operacao'));
            $files->put(base_path('docs/operacao/legacy_cutoff_readiness_report.json'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $files->put(base_path('docs/operacao/legacy_cutoff_readiness_report.md'), $this->toMarkdown($report));
            $this->info('Relatórios gravados em docs/operacao/legacy_cutoff_readiness_report.json e .md');
        }

        return self::SUCCESS;
    }

    private function toMarkdown(array $report): string
    {
        $md = "# Legacy Cutoff Readiness Report\n\n";
        $md .= '- generated_at: ' . $report['generated_at'] . "\n";
        $md .= '- ready_for_safe_cutoff: ' . ($report['overall']['ready_for_safe_cutoff'] ? 'true' : 'false') . "\n";
        $md .= '- next_step: ' . $report['overall']['next_step'] . "\n\n";
        $md .= "## Stock Write Block\n";
        $md .= '- ready: ' . ($report['stock_write_block']['ready'] ? 'true' : 'false') . "\n";
        $md .= '- violation_count: ' . $report['stock_write_block']['violation_count'] . "\n\n";
        $md .= "## Public Surface Findings\n";
        foreach ($report['public_surface']['findings'] as $finding) {
            $md .= '- ' . $finding['path'] . ': ' . $finding['message'] . "\n";
        }

        return $md;
    }
}
