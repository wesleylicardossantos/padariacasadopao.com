<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class RefatoracaoFinalAuditCommand extends Command
{
    protected $signature = 'refatoracao:final-audit {--write : grava relatório em storage/app/refatoracao_final_audit.json}';

    protected $description = 'Auditoria final da refatoração: banco, RH, portal, folha, dossiê, RBAC e rotas registradas.';

    public function handle(): int
    {
        $requiredTables = [
            'funcionarios', 'evento_salarios', 'funcionario_eventos', 'apuracao_mensals',
            'rh_folha_itens', 'rh_portal_funcionarios', 'rh_portal_perfis', 'rh_dossies',
            'rh_dossie_eventos', 'rh_acl_papeis', 'rh_acl_permissoes', 'rh_acl_papel_permissoes',
            'rh_acl_papel_usuarios', 'migrations',
        ];

        $requiredMigrations = [
            '2026_04_25_220000_refatoracao_final_reconcile_rh_core_tables',
            '2026_04_25_220100_refatoracao_final_reconcile_rh_acl_tables',
        ];

        $tables = [];
        foreach ($requiredTables as $table) {
            $tables[$table] = Schema::hasTable($table);
        }

        $migrations = [];
        if (Schema::hasTable('migrations')) {
            foreach ($requiredMigrations as $migration) {
                $migrations[$migration] = DB::table('migrations')->where('migration', $migration)->exists();
            }
        }

        $routes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'methods' => $route->methods(),
            ];
        });

        $rhRoutes = $routes->filter(function ($route) {
            return str_contains((string) $route['uri'], 'rh')
                || str_contains((string) $route['uri'], 'portal')
                || str_contains((string) $route['action'], 'RH');
        })->values();

        $missingTables = array_keys(array_filter($tables, fn ($ok) => !$ok));
        $missingMigrations = array_keys(array_filter($migrations, fn ($ok) => !$ok));

        $report = [
            'status' => empty($missingTables) && empty($missingMigrations) ? 'concluida_com_validacao_logica' : 'pendente_de_reconciliacao',
            'generated_at' => now()->toDateTimeString(),
            'required_tables' => $tables,
            'missing_tables' => $missingTables,
            'required_migrations' => $migrations,
            'missing_migrations' => $missingMigrations,
            'registered_routes_total' => $routes->count(),
            'rh_routes_total' => $rhRoutes->count(),
            'critical_routes_sample' => $rhRoutes->take(80)->all(),
        ];

        $this->info('Status: '.$report['status']);
        $this->line('Tabelas ausentes: '.(empty($missingTables) ? 'nenhuma' : implode(', ', $missingTables)));
        $this->line('Migrations finais ausentes: '.(empty($missingMigrations) ? 'nenhuma' : implode(', ', $missingMigrations)));
        $this->line('Rotas registradas: '.$report['registered_routes_total']);
        $this->line('Rotas RH/Portal detectadas: '.$report['rh_routes_total']);

        if ($this->option('write')) {
            $path = storage_path('app/refatoracao_final_audit.json');
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0775, true);
            }
            file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info('Relatório gravado em: '.$path);
        }

        return empty($missingTables) && empty($missingMigrations) ? self::SUCCESS : self::FAILURE;
    }
}
