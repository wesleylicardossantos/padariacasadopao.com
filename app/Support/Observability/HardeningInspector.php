<?php

namespace App\Support\Observability;

use App\Support\Routing\RouteFileRegistry;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class HardeningInspector
{
    public function __construct(
        protected Filesystem $files,
    ) {
    }

    public function build(): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'flags' => [
                'stock_monitor_direct_legacy_writes' => (bool) config('stock_governance.monitor_direct_legacy_writes', true),
                'stock_block_direct_legacy_writes' => (bool) config('stock_governance.block_direct_legacy_writes', false),
                'hardening_enforce_public_surface_review' => (bool) config('hardening.final.enforce_public_surface_review', true),
                'hardening_index_review_enabled' => (bool) config('hardening.final.index_review_enabled', true),
            ],
            'public_surface' => $this->inspectPublicSurface(),
            'index_coverage' => $this->inspectIndexCoverage(),
            'dead_code_candidates' => $this->inspectDeadCodeCandidates(),
            'security_headers' => $this->inspectSecurityHeaders(),
            'artifacts' => $this->inspectArtifacts(),
        ];
    }

    protected function inspectPublicSurface(): array
    {
        $findings = [];

        $publicCandidates = [
            public_path('clear.php'),
            public_path('default.php'),
        ];

        foreach ($publicCandidates as $path) {
            if ($this->files->exists($path)) {
                $findings[] = [
                    'type' => 'public_script',
                    'severity' => 'high',
                    'path' => $this->normalizePath($path),
                    'message' => 'Script público sensível detectado; revisar necessidade e proteger acesso.',
                ];
            }
        }

        foreach ([base_path('routes'), app_path('Modules')] as $root) {
            if (!$this->files->exists($root)) {
                continue;
            }

            foreach ($this->files->allFiles($root) as $file) {
                if (!Str::endsWith($file->getFilename(), '.php')) {
                    continue;
                }

                $normalized = $this->normalizePath($file->getRealPath());
                $content = $this->files->get($file->getRealPath());
                foreach (['clear-all', 'optimize:clear', 'migrate:fresh', 'db:wipe'] as $needle) {
                    if (!Str::contains($content, $needle)) {
                        continue;
                    }

                    if ($this->isProtectedOperationalReference($normalized, $needle, $content)) {
                        continue;
                    }

                    $findings[] = [
                        'type' => 'route_or_code_keyword',
                        'severity' => in_array($needle, ['migrate:fresh', 'db:wipe'], true) ? 'high' : 'medium',
                        'path' => $normalized,
                        'message' => "Palavra-chave operacional sensível detectada: {$needle}",
                    ];
                }
            }
        }

        return [
            'count' => count($findings),
            'findings' => $findings,
        ];
    }

    protected function inspectIndexCoverage(): array
    {
        $matrix = [
            'pdv_offline_syncs' => ['empresa_id', 'uuid_local', 'status'],
            'stock_movements' => ['empresa_id', 'filial_id', 'produto_id', 'referencia_id'],
            'financial_audits' => ['empresa_id', 'entidade', 'entidade_id'],
            'commercial_audits' => ['empresa_id', 'entidade', 'entidade_id'],
            'fiscal_documents' => ['empresa_id', 'status', 'numero_documento'],
            'fiscal_audits' => ['empresa_id', 'fiscal_document_id', 'acao'],
        ];

        $coverage = [];
        foreach ($matrix as $table => $columns) {
            $tableCoverage = [
                'table' => $table,
                'exists' => $this->safeTableExists($table),
                'columns' => [],
            ];

            if ($tableCoverage['exists']) {
                $indexes = $this->safeIndexes($table);
                foreach ($columns as $column) {
                    $tableCoverage['columns'][$column] = in_array($column, $indexes, true);
                }
            }

            $coverage[] = $tableCoverage;
        }

        return $coverage;
    }

    protected function inspectDeadCodeCandidates(): array
    {
        $patterns = ['_patch_', '.bak', '.old', '.backup', 'copy', 'tudo_nivel_maximo'];
        $roots = [app_path(), base_path('routes')];
        $candidates = [];

        foreach ($roots as $root) {
            if (!$this->files->exists($root)) {
                continue;
            }

            foreach ($this->files->allFiles($root) as $file) {
                $name = Str::lower($file->getFilename());
                foreach ($patterns as $pattern) {
                    if (!Str::contains($name, Str::lower($pattern))) {
                        continue;
                    }

                    $normalized = $this->normalizePath($file->getRealPath());
                    if ($this->isKnownOptionalArtifact($normalized)) {
                        continue;
                    }

                    $candidates[] = [
                        'path' => $normalized,
                        'reason' => "Nome sugere código temporário/duplicado: {$pattern}",
                    ];
                    break;
                }
            }
        }

        return [
            'count' => count($candidates),
            'candidates' => $candidates,
        ];
    }

    protected function isProtectedOperationalReference(string $normalizedPath, string $needle, string $content): bool
    {
        if (Str::startsWith($normalizedPath, 'routes/aliases/')) {
            return true;
        }

        if ($needle === 'clear-all' && Str::contains($content, 'restrictMaintenance')) {
            return true;
        }

        return false;
    }

    protected function isKnownOptionalArtifact(string $normalizedPath): bool
    {
        if (Str::endsWith($normalizedPath, '.bak')) {
            return false;
        }

        $optionalRouteFiles = array_map(
            fn (string $path) => $this->normalizePath($path),
            RouteFileRegistry::optionalFiles()
        );

        if (in_array($normalizedPath, $optionalRouteFiles, true) && !RouteFileRegistry::shouldLoadPatchRoutes()) {
            return true;
        }

        if (Str::startsWith($normalizedPath, 'routes/patches/') && !RouteFileRegistry::shouldLoadPatchRoutes()) {
            return true;
        }

        return Str::startsWith($normalizedPath, 'app/Helpers/menu_patch_');
    }


    protected function inspectSecurityHeaders(): array
    {
        $middlewareFile = app_path('Http/Middleware/SecurityHeaders.php');
        $kernel = $this->files->exists(app_path('Http/Kernel.php'))
            ? $this->files->get(app_path('Http/Kernel.php'))
            : '';

        return [
            'middleware_exists' => $this->files->exists($middlewareFile),
            'kernel_registered' => Str::contains($kernel, '\App\Http\Middleware\SecurityHeaders::class'),
            'headers' => [
                'x_frame_options' => (string) config('hardening.security_headers.x_frame_options', 'SAMEORIGIN'),
                'referrer_policy' => (string) config('hardening.security_headers.referrer_policy', 'strict-origin-when-cross-origin'),
                'permissions_policy' => (string) config('hardening.security_headers.permissions_policy', ''),
                'csp_report_only_enabled' => trim((string) config('hardening.security_headers.content_security_policy_report_only', '')) !== '',
                'hsts_enabled' => (bool) config('hardening.security_headers.enable_hsts', false),
            ],
        ];
    }

    protected function inspectArtifacts(): array
    {
        $paths = [
            'docs/operacao/governance_report.json',
            'docs/operacao/schema_drift_report.json',
            'docs/operacao/system_healthcheck.json',
            'docs/operacao/stock_governance_report.json',
            'docs/operacao/fiscal_operations_report.json',
        ];

        $artifacts = [];
        foreach ($paths as $relative) {
            $artifacts[] = [
                'path' => $relative,
                'exists' => $this->files->exists(base_path($relative)),
            ];
        }

        return $artifacts;
    }

    protected function safeTableExists(string $table): bool
    {
        try {
            return \Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    protected function safeIndexes(string $table): array
    {
        try {
            $driver = config('database.default');
            $connection = config("database.connections.{$driver}.driver");
            if ($connection === 'sqlite') {
                $rows = \DB::select("PRAGMA index_list('{$table}')");
                $columns = [];
                foreach ($rows as $row) {
                    $name = Arr::get((array) $row, 'name');
                    if (!$name) {
                        continue;
                    }
                    foreach (\DB::select("PRAGMA index_info('{$name}')") as $col) {
                        $columns[] = Arr::get((array) $col, 'name');
                    }
                }

                return array_values(array_unique(array_filter($columns)));
            }

            $rows = \DB::select("SHOW INDEX FROM `{$table}`");
            return array_values(array_unique(array_filter(array_map(fn ($row) => Arr::get((array) $row, 'Column_name'), $rows))));
        } catch (Throwable) {
            return [];
        }
    }

    protected function normalizePath(string $path): string
    {
        return Str::replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
    }
}
