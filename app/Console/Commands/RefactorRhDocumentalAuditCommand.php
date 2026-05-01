<?php

namespace App\Console\Commands;

use App\Http\Controllers\RHDossieController;
use App\Http\Controllers\RHDocumentoGeradoController;
use App\Http\Controllers\RHPortalFuncionarioController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RefactorRhDocumentalAuditCommand extends Command
{
    protected $signature = 'refactor:rh-documental-audit {--write : Persiste o relatório em docs/refatoracao}';

    protected $description = 'Audita a camada documental do RH: dossiê, holerite, rescisão e portal externo.';

    public function handle(): int
    {
        $routesByName = collect(app('router')->getRoutes()->getRoutesByName());
        $routeTargets = [
            'rh.dossie.show',
            'rh.dossie.documentos.store',
            'rh.dossie.documentos.download',
            'rh.portal_externo.documentos_rescisao',
            'rh.portal_externo.documentos_rescisao.trct.pdf',
            'rh.portal_externo.documentos_rescisao.tqrct.pdf',
            'rh.portal_externo.documentos_rescisao.homologacao.pdf',
            'rh.portal_funcionario.pdf',
        ];

        $routes = [];
        foreach ($routeTargets as $target) {
            $route = $routesByName->get($target);
            $routes[] = [
                'route' => $target,
                'exists' => (bool) $route,
                'middleware' => $route ? array_values($route->gatherMiddleware()) : [],
            ];
        }

        $tables = [
            'rh_dossies' => ['empresa_id', 'funcionario_id', 'status', 'ultima_atualizacao_em'],
            'rh_dossie_eventos' => ['empresa_id', 'funcionario_id', 'categoria', 'titulo', 'data_evento', 'visibilidade_portal'],
            'rh_documentos' => ['empresa_id', 'funcionario_id', 'tipo', 'categoria', 'arquivo', 'origem', 'status', 'hash_conteudo'],
            'rh_rescisoes' => ['empresa_id', 'funcionario_id', 'data_rescisao', 'total_liquido', 'status'],
        ];

        $tableChecks = [];
        foreach ($tables as $table => $columns) {
            $exists = false;
            $schemaError = null;
            $columnChecks = [];

            try {
                $exists = Schema::hasTable($table);
            } catch (Throwable $e) {
                $schemaError = $e->getMessage();
            }

            foreach ($columns as $column) {
                $columnExists = false;
                try {
                    $columnExists = $schemaError === null && $exists ? Schema::hasColumn($table, $column) : false;
                } catch (Throwable $e) {
                    $schemaError = $schemaError ?: $e->getMessage();
                }

                $columnChecks[] = [
                    'column' => $column,
                    'exists' => $columnExists,
                ];
            }

            $tableChecks[] = [
                'table' => $table,
                'exists' => $exists,
                'schema_error' => $schemaError,
                'columns' => $columnChecks,
            ];
        }

        $views = [
            'resources/views/rh/dossie/show.blade.php',
            'resources/views/rh/holerite/pdf.blade.php',
            'resources/views/rh/portal_funcionario/documentos_rescisao_externo.blade.php',
            'resources/views/rh/documentos/pdf/documento_rescisao_pdf.blade.php',
            'resources/views/rh/documentos/pdf/trct_juridico_pdf.blade.php',
        ];
        $viewChecks = array_map(fn ($view) => ['view' => $view, 'exists' => File::exists(base_path($view))], $views);

        $controllers = [
            RHDossieController::class,
            RHPortalFuncionarioController::class,
            RHDocumentoGeradoController::class,
        ];
        $controllerChecks = array_map(fn ($controller) => [
            'controller' => $controller,
            'exists' => class_exists($controller),
        ], $controllers);

        $report = [
            'generated_at' => now()->toDateTimeString(),
            'routes' => $routes,
            'tables' => $tableChecks,
            'views' => $viewChecks,
            'controllers' => $controllerChecks,
        ];

        foreach ($routes as $route) {
            $this->line(sprintf('- %s | existe=%s', $route['route'], $route['exists'] ? 'sim' : 'nao'));
        }
        foreach ($tableChecks as $table) {
            $this->line(sprintf('- tabela %s | existe=%s', $table['table'], $table['exists'] ? 'sim' : 'nao'));
        }

        if ($this->option('write')) {
            File::ensureDirectoryExists(base_path('docs/refatoracao'));

            $lines = [
                '# Auditoria documental RH',
                '',
                '- Gerado em: ' . $report['generated_at'],
                '',
                '## Rotas auditadas',
                '',
            ];
            foreach ($routes as $route) {
                $lines[] = sprintf('- %s | existe=%s', $route['route'], $route['exists'] ? 'sim' : 'nao');
            }
            $lines[] = '';
            $lines[] = '## Tabelas auditadas';
            $lines[] = '';
            foreach ($tableChecks as $table) {
                $lines[] = sprintf('- %s | existe=%s', $table['table'], $table['exists'] ? 'sim' : 'nao');
                if (!empty($table['schema_error'])) {
                    $lines[] = '  - schema_error=' . $table['schema_error'];
                }
                foreach ($table['columns'] as $column) {
                    $lines[] = sprintf('  - %s=%s', $column['column'], $column['exists'] ? 'sim' : 'nao');
                }
            }

            File::put(base_path('docs/refatoracao/rh_documental_audit_2026-04-23.md'), implode(PHP_EOL, $lines) . PHP_EOL);
            File::put(base_path('docs/refatoracao/rh_documental_audit_2026-04-23.json'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return self::SUCCESS;
    }
}
