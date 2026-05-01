<?php

namespace App\Support\Cutoff;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LegacyCutoffReadinessInspector
{
    public function __construct(
        private readonly Filesystem $files,
    ) {
    }

    public function build(): array
    {
        $stockViolations = $this->recentStockViolations();
        $reports = $this->requiredReports();
        $publicSurface = $this->publicSurfaceFindings();

        $stockReady = empty($stockViolations);
        $reportsReady = collect($reports)->every(fn (array $item) => $item['exists'] === true);
        $publicSurfaceReady = count($publicSurface) === 0;

        return [
            'generated_at' => now()->toIso8601String(),
            'flags' => [
                'stock_monitor_direct_legacy_writes' => (bool) config('stock_governance.monitor_direct_legacy_writes', true),
                'stock_block_direct_legacy_writes' => (bool) config('stock_governance.block_direct_legacy_writes', false),
                'legacy_cutoff_allow_force_enable' => (bool) config('cutoff.allow_force_enable', false),
            ],
            'stock_write_block' => [
                'ready' => $stockReady,
                'violation_count' => count($stockViolations),
                'recent_violations' => $stockViolations,
                'recommended_action' => $stockReady
                    ? 'Pode avançar para ativação controlada do bloqueio de escrita direta no legado.'
                    : 'Corrija as violações recentes antes de ativar STOCK_BLOCK_DIRECT_LEGACY_WRITES.',
            ],
            'reports' => [
                'ready' => $reportsReady,
                'items' => $reports,
            ],
            'public_surface' => [
                'ready' => $publicSurfaceReady,
                'findings' => $publicSurface,
            ],
            'overall' => [
                'ready_for_safe_cutoff' => $stockReady && $reportsReady && $publicSurfaceReady,
                'next_step' => $stockReady && $reportsReady && $publicSurfaceReady
                    ? 'Ativar o bloqueio primeiro em homologação e depois em produção com janela monitorada.'
                    : 'Manter observação, corrigir hotspots/violações e reavaliar.',
            ],
        ];
    }

    private function recentStockViolations(): array
    {
        try {
            if (!Schema::hasTable('stock_write_audits')) {
                return [];
            }

            return DB::table('stock_write_audits')
                ->select('id', 'empresa_id', 'filial_id', 'produto_id', 'event', 'guard_source', 'request_path', 'notes', 'created_at')
                ->where('guard_allowed', false)
                ->orderByDesc('id')
                ->limit(25)
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    private function requiredReports(): array
    {
        $paths = [
            'docs/operacao/governance_report.json',
            'docs/operacao/schema_drift_report.json',
            'docs/operacao/system_healthcheck.json',
            'docs/operacao/stock_governance_report.json',
            'docs/operacao/hardening_final_report.json',
        ];

        return array_map(function (string $relative): array {
            return [
                'path' => $relative,
                'exists' => $this->files->exists(base_path($relative)),
            ];
        }, $paths);
    }

    private function publicSurfaceFindings(): array
    {
        $findings = [];
        foreach ([public_path('clear.php'), base_path('clear.php'), public_path('default.php')] as $path) {
            if ($this->files->exists($path)) {
                $findings[] = [
                    'path' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path),
                    'message' => 'Script público sensível ainda presente; revisar proteção ou remoção controlada.',
                ];
            }
        }

        return $findings;
    }
}
