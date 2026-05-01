<?php

namespace App\Console\Commands;

use App\Modules\Fiscal\Services\FiscalOperationsReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class FiscalOperationsReportCommand extends Command
{
    protected $signature = 'fiscal:operations-report {empresa_id=1} {--write}';

    protected $description = 'Gera resumo operacional do domínio Fiscal estabilizado por facade/adapters';

    public function handle(FiscalOperationsReportService $service): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        try {
            $summary = $service->summary($empresaId);
            $summary['database_available'] = true;
            $summary['database_error'] = null;
        } catch (Throwable $e) {
            $summary = [
                'empresa_id' => $empresaId,
                'documents_total' => 0,
                'prepared_total' => 0,
                'transmitted_total' => 0,
                'cancelled_total' => 0,
                'latest_documents' => [],
                'audit_total' => 0,
                'updated_at' => now()->toDateTimeString(),
                'database_available' => false,
                'database_error' => $e->getMessage(),
            ];
        }

        $this->info('Resumo fiscal gerado para empresa '.$empresaId);
        $this->line(json_encode($summary, JSON_UNESCAPED_UNICODE));

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/operacao'));
            File::put(base_path('docs/operacao/fiscal_operations_report.json'), json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            File::put(base_path('docs/operacao/fiscal_operations_report.md'), $this->toMarkdown($summary));
        }

        return self::SUCCESS;
    }

    private function toMarkdown(array $summary): string
    {
        return implode("\n", [
            '# Fiscal Operations Report',
            '',
            '- Empresa: '.$summary['empresa_id'],
            '- Documentos totais: '.$summary['documents_total'],
            '- Preparados: '.$summary['prepared_total'],
            '- Transmitidos: '.$summary['transmitted_total'],
            '- Cancelados: '.$summary['cancelled_total'],
            '- Auditorias: '.$summary['audit_total'],
            '- Gerado em: '.$summary['updated_at'],
            '',
            '## Últimos documentos',
            '',
            '```json',
            json_encode($summary['latest_documents'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            '```',
            '',
        ]);
    }
}
