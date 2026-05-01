<?php

namespace App\Modules\Estoque\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StockGovernanceReportService
{
    public function generate(bool $write = false): array
    {
        $hotspots = $this->detectHotspots();
        $violations = $this->recentViolations();
        $summary = [
            'generated_at' => now()->toDateTimeString(),
            'hotspots' => $hotspots,
            'recent_violation_count' => count($violations),
            'recent_violations' => $violations,
            'blocking_enabled' => (bool) config('stock_governance.block_direct_legacy_writes', false),
            'monitoring_enabled' => (bool) config('stock_governance.monitor_direct_legacy_writes', true),
        ];

        if ($write) {
            $relative = config('stock_governance.report_path', 'docs/operacao/stock_governance_report.json');
            $path = base_path($relative);
            File::ensureDirectoryExists(dirname($path));
            File::put($path, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return $summary;
    }

    private function recentViolations(): array
    {
        try {
            if (!Schema::hasTable('stock_write_audits')) {
                return [];
            }

            return DB::table('stock_write_audits')
                ->select('id', 'empresa_id', 'filial_id', 'produto_id', 'event', 'guard_allowed', 'guard_source', 'request_path', 'notes', 'created_at')
                ->where('guard_allowed', false)
                ->orderByDesc('id')
                ->limit(50)
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    private function detectHotspots(): array
    {
        $patterns = [
            "Estoque::create(",
            "Estoque::query()->updateOrCreate(",
            "DB::table('estoques')->insert(",
            "DB::table('estoques')->update(",
            "->quantidade =",
        ];

        $files = File::allFiles(app_path());
        $matches = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getRealPath());
            foreach ($patterns as $pattern) {
                if (str_contains($content, $pattern)) {
                    $matches[] = [
                        'file' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath()),
                        'pattern' => $pattern,
                    ];
                }
            }
        }

        return $matches;
    }
}
