<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RefactorPhase3AuditCommand extends Command
{
    protected $signature = 'refactor:phase3-audit {--write : Salva relatório em docs/operacao}';

    protected $description = 'Audita o fechamento da fase 3: financeiro via use cases, hotspots de estoque e blindagem PDV offline.';

    public function handle(): int
    {
        $base = base_path();
        $report = [
            'generated_at' => now()->toDateTimeString(),
            'financeiro' => [
                'legacy_receivable_bridge_uses_register_usecase' => $this->fileContains('app/Modules/Financeiro/Services/LegacyBridge/LegacyReceivableBridgeService.php', 'RegisterReceivableUseCase'),
                'legacy_receivable_bridge_uses_update_usecase' => $this->fileContains('app/Modules/Financeiro/Services/LegacyBridge/LegacyReceivableBridgeService.php', 'UpdateReceivableUseCase'),
                'legacy_payable_bridge_uses_register_usecase' => $this->fileContains('app/Modules/Financeiro/Services/LegacyBridge/LegacyPayableBridgeService.php', 'RegisterPayableUseCase'),
                'legacy_payable_bridge_uses_update_usecase' => $this->fileContains('app/Modules/Financeiro/Services/LegacyBridge/LegacyPayableBridgeService.php', 'UpdatePayableUseCase'),
                'conta_receber_controller_uses_bridge' => $this->fileContains('app/Http/Controllers/ContaReceberController.php', 'LegacyReceivableBridgeService'),
                'conta_pagar_controller_uses_bridge' => $this->fileContains('app/Http/Controllers/ContaPagarController.php', 'LegacyPayableBridgeService'),
            ],
            'estoque' => [
                'product_controller_uses_stock_ledger' => $this->fileContains('app/Http/Controllers/ProductController.php', 'StockLedgerService::class'),
                'remaining_direct_estoque_creates' => $this->countAcrossApp('Estoque::create(['),
                'remaining_direct_estoque_update_or_create' => $this->countAcrossApp('Estoque::query()->updateOrCreate('),
            ],
            'pdv_offline' => [
                'sync_service_uses_lock_for_update' => $this->fileContains('app/Modules/PDV/Services/OfflineSaleSyncService.php', 'lockForUpdate()'),
                'sync_service_checks_payload_hash_conflict' => $this->fileContains('app/Modules/PDV/Services/OfflineSaleSyncService.php', 'STATUS_CONFLITO_PAYLOAD'),
                'uuid_empresa_unique_migration_present' => $this->fileContains('database/migrations/2026_03_21_144500_fix_pdv_offline_syncs_missing_columns.php', "unique(['empresa_id', 'uuid_local']"),
            ],
        ];

        $report['resumo'] = [
            'financeiro_ok' => !in_array(false, $report['financeiro'], true),
            'estoque_hotspots_residuais' => ($report['estoque']['remaining_direct_estoque_creates'] ?? 0) + ($report['estoque']['remaining_direct_estoque_update_or_create'] ?? 0),
            'pdv_offline_ok' => !in_array(false, $report['pdv_offline'], true),
        ];

        if ($this->option('write')) {
            File::ensureDirectoryExists($base . '/docs/operacao');
            File::put($base . '/docs/operacao/refactor_phase3_audit.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            File::put($base . '/docs/operacao/refactor_phase3_audit.md', $this->toMarkdown($report));
            $this->info('Relatório salvo em docs/operacao/refactor_phase3_audit.{json,md}');
        }

        $this->line('Financeiro via use cases: ' . ($report['resumo']['financeiro_ok'] ? 'OK' : 'PENDENTE'));
        $this->line('Hotspots residuais de estoque: ' . $report['resumo']['estoque_hotspots_residuais']);
        $this->line('PDV offline blindado: ' . ($report['resumo']['pdv_offline_ok'] ? 'OK' : 'PENDENTE'));

        return self::SUCCESS;
    }

    private function fileContains(string $relativePath, string $needle): bool
    {
        $path = base_path($relativePath);
        if (!File::exists($path)) {
            return false;
        }

        return str_contains(File::get($path), $needle);
    }

    private function countAcrossApp(string $needle): int
    {
        $total = 0;
        foreach (File::allFiles(app_path()) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $total += substr_count(File::get($file->getRealPath()), $needle);
        }

        return $total;
    }

    private function toMarkdown(array $report): string
    {
        return "# Auditoria da Fase 3\n\n"
            . "Gerado em: {$report['generated_at']}\n\n"
            . "## Resumo\n"
            . "- Financeiro via use cases: " . ($report['resumo']['financeiro_ok'] ? 'OK' : 'PENDENTE') . "\n"
            . "- Hotspots residuais de estoque: {$report['resumo']['estoque_hotspots_residuais']}\n"
            . "- PDV offline blindado: " . ($report['resumo']['pdv_offline_ok'] ? 'OK' : 'PENDENTE') . "\n\n"
            . "## Financeiro\n```json\n" . json_encode($report['financeiro'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n```\n\n"
            . "## Estoque\n```json\n" . json_encode($report['estoque'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n```\n\n"
            . "## PDV offline\n```json\n" . json_encode($report['pdv_offline'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n```\n";
    }
}
