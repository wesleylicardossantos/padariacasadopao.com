<?php

namespace App\Console\Commands;

use App\Support\Observability\HardeningInspector;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class HardeningFinalReportCommand extends Command
{
    protected $signature = 'hardening:final-report {--write : Grava os artefatos em docs/operacao}';

    protected $description = 'Gera o relatório final de hardening e readiness da refatoração.';

    public function handle(HardeningInspector $inspector, Filesystem $files): int
    {
        $report = $inspector->build();

        $this->info('Hardening final da refatoração');
        $this->line('Findings de superfície pública: ' . ($report['public_surface']['count'] ?? 0));
        $this->line('Candidatos a código morto/temporário: ' . ($report['dead_code_candidates']['count'] ?? 0));

        if ($this->option('write')) {
            $dir = base_path('docs/operacao');
            $files->ensureDirectoryExists($dir);
            $files->put($dir . '/hardening_final_report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $files->put($dir . '/hardening_final_report.md', $this->toMarkdown($report));
            $this->info('Relatórios gravados em docs/operacao');
        }

        return self::SUCCESS;
    }

    protected function toMarkdown(array $report): string
    {
        $md = "# Hardening final da refatoração\n\n";
        $md .= "Gerado em: {$report['generated_at']}\n\n";
        $md .= "## Flags\n";
        foreach ($report['flags'] as $flag => $value) {
            $md .= '- ' . $flag . ': ' . ($value ? 'true' : 'false') . "\n";
        }

        $md .= "\n## Superfície pública\n";
        if (($report['public_surface']['count'] ?? 0) === 0) {
            $md .= "- Nenhum achado crítico mapeado.\n";
        } else {
            foreach ($report['public_surface']['findings'] as $finding) {
                $md .= '- [' . ($finding['severity'] ?? 'info') . '] ' . ($finding['path'] ?? '-') . ' — ' . ($finding['message'] ?? '') . "\n";
            }
        }

        $md .= "\n## Security headers\n";
        $md .= '- middleware_exists: ' . (($report['security_headers']['middleware_exists'] ?? false) ? 'true' : 'false') . "\n";
        $md .= '- kernel_registered: ' . (($report['security_headers']['kernel_registered'] ?? false) ? 'true' : 'false') . "\n";
        foreach (($report['security_headers']['headers'] ?? []) as $header => $value) {
            $md .= '- ' . $header . ': ' . (is_bool($value) ? ($value ? 'true' : 'false') : (string) $value) . "\n";
        }

        $md .= "\n## Cobertura de índices\n";
        foreach ($report['index_coverage'] as $item) {
            $md .= "### {$item['table']}\n";
            if (!$item['exists']) {
                $md .= "- tabela ausente\n";
                continue;
            }
            foreach ($item['columns'] as $column => $covered) {
                $md .= '- ' . $column . ': ' . ($covered ? 'ok' : 'missing') . "\n";
            }
        }

        $md .= "\n## Candidatos a código morto/temporário\n";
        if (($report['dead_code_candidates']['count'] ?? 0) === 0) {
            $md .= "- Nenhum candidato detectado.\n";
        } else {
            foreach ($report['dead_code_candidates']['candidates'] as $candidate) {
                $md .= '- ' . ($candidate['path'] ?? '-') . ' — ' . ($candidate['reason'] ?? '') . "\n";
            }
        }

        $md .= "\n## Artefatos esperados\n";
        foreach ($report['artifacts'] as $artifact) {
            $md .= '- ' . $artifact['path'] . ': ' . ($artifact['exists'] ? 'ok' : 'missing') . "\n";
        }

        return $md;
    }
}
