<?php

namespace App\Console\Commands;

use App\Support\Observability\OperationalChecklist;
use App\Support\Routing\ProjectInventory;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class RefactorGovernanceReportCommand extends Command
{
    protected $signature = 'refactor:governance-report {--write : Grava relatórios em docs/operacao}';

    protected $description = 'Gera o pacote de governança operacional da refatoração.';

    public function handle(ProjectInventory $inventory, Filesystem $files): int
    {
        $report = [
            'generated_at' => now()->toIso8601String(),
            'inventory_summary' => $inventory->build()['metrics'],
            'deploy_checklist' => OperationalChecklist::deploy(),
            'smoke_tests' => OperationalChecklist::smoke(),
        ];

        $this->info('Governança da refatoração');
        $this->line('Controllers: ' . ($report['inventory_summary']['controllers'] ?? 0));
        $this->line('Models: ' . ($report['inventory_summary']['models'] ?? 0));
        $this->line('Services: ' . (($report['inventory_summary']['services'] ?? 0) + ($report['inventory_summary']['module_services'] ?? 0)));
        $this->line('Checklist de deploy: ' . count($report['deploy_checklist']['before']) . ' itens antes / ' . count($report['deploy_checklist']['during']) . ' durante / ' . count($report['deploy_checklist']['after']) . ' depois');

        if ($this->option('write')) {
            $dir = base_path('docs/operacao');
            $files->ensureDirectoryExists($dir);
            $files->put($dir . '/governance_report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $files->put($dir . '/deploy_checklist.md', $this->renderChecklistMarkdown($report));
            $this->info('Relatórios gravados em docs/operacao');
        }

        return self::SUCCESS;
    }

    private function renderChecklistMarkdown(array $report): string
    {
        $md = "# Governança operacional da refatoração\n\n";
        $md .= "Gerado em: {$report['generated_at']}\n\n";
        $md .= "## Inventário resumido\n";
        foreach ($report['inventory_summary'] as $label => $value) {
            $md .= "- {$label}: {$value}\n";
        }

        $md .= "\n## Checklist de deploy\n";
        foreach ($report['deploy_checklist'] as $section => $items) {
            $md .= "\n### {$section}\n";
            foreach ($items as $item) {
                $md .= "- [ ] {$item}\n";
            }
        }

        $md .= "\n## Smoke tests mínimos\n";
        foreach ($report['smoke_tests'] as $section => $items) {
            $md .= "\n### {$section}\n";
            foreach ($items as $item) {
                $md .= "- [ ] {$item}\n";
            }
        }

        return $md;
    }
}
