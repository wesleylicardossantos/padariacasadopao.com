<?php

namespace App\Support\Cutoff;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class PerformanceBaselineInspector
{
    public function __construct(
        private readonly Filesystem $files,
    ) {
    }

    public function build(): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'cache' => [
                'default_driver' => (string) config('cache.default'),
                'config_cached' => app()->configurationIsCached(),
                'routes_cached' => app()->routesAreCached(),
                'cache_table_exists' => Schema::hasTable('cache'),
                'cache_locks_table_exists' => Schema::hasTable('cache_locks'),
            ],
            'queue' => [
                'connection' => (string) config('queue.default'),
                'jobs_table_exists' => Schema::hasTable('jobs'),
                'failed_jobs_table_exists' => Schema::hasTable('failed_jobs'),
                'job_batches_table_exists' => Schema::hasTable('job_batches'),
            ],
            'infra' => [
                'redis_enabled' => (bool) config('infra.redis_enabled', false),
                'queue_enabled' => (bool) config('infra.queue_enabled', true),
                'slow_query_monitor_enabled' => (bool) config('infra.slow_query.enabled', true),
                'slow_query_threshold_ms' => (int) config('infra.slow_query.threshold_ms', 350),
                'performance_events_table_exists' => Schema::hasTable('performance_events'),
            ],
            'database' => [
                'driver' => (string) config('database.connections.' . config('database.default') . '.driver'),
                'critical_tables' => $this->criticalTableStats(),
            ],
            'query_hotspots' => $this->queryHotspots(),
            'route_surface' => $this->routeSurfaceStats(),
            'recommendations' => $this->recommendations(),
        ];
    }

    private function criticalTableStats(): array
    {
        $tables = ['vendas', 'venda_caixas', 'item_venda_caixas', 'estoques', 'stock_movements', 'pdv_offline_syncs', 'conta_recebers', 'conta_pagars', 'performance_events'];
        $stats = [];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $stats[] = ['table' => $table, 'exists' => false];
                continue;
            }

            $row = ['table' => $table, 'exists' => true];
            try {
                $row['estimated_rows'] = (int) DB::table($table)->count();
            } catch (Throwable) {
                $row['estimated_rows'] = null;
            }
            $row['indexed_columns'] = $this->safeIndexes($table);
            $stats[] = $row;
        }

        return $stats;
    }

    private function queryHotspots(): array
    {
        $patterns = [
            'orderByRaw(' => 'Uso de ORDER BY raw; revisar índice/custo.',
            'groupByRaw(' => 'Uso de GROUP BY raw; revisar plano de execução.',
            'DB::raw(' => 'Uso de DB::raw em query de aplicação.',
            '->get()' => 'Coleta integral; avaliar paginação/chunk em listagens grandes.',
            '->all()' => 'Carga completa em memória; revisar se há conjuntos grandes.',
            'paginate(' => 'Paginação detectada; validar índices de filtros.',
            'whereDate(' => 'whereDate pode degradar uso de índice em coluna de data/datetime.',
        ];

        $matches = [];
        foreach ($this->files->allFiles(app_path()) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = $this->files->get($file->getRealPath());
            foreach ($patterns as $needle => $note) {
                if (Str::contains($content, $needle)) {
                    $matches[] = [
                        'file' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath()),
                        'pattern' => $needle,
                        'note' => $note,
                    ];
                }
            }
        }

        return $matches;
    }

    private function routeSurfaceStats(): array
    {
        $routeFiles = $this->files->allFiles(base_path('routes'));

        return [
            'route_file_count' => count($routeFiles),
            'enterprise_route_files' => collect($routeFiles)
                ->filter(fn ($file) => Str::contains($file->getFilename(), 'enterprise_'))
                ->count(),
        ];
    }

    private function recommendations(): array
    {
        $items = [];

        if (!app()->configurationIsCached()) {
            $items[] = 'Executar php artisan config:cache em produção após deploy validado.';
        }
        if (!app()->routesAreCached()) {
            $items[] = 'Executar php artisan route:cache se não houver rotas dinâmicas incompatíveis.';
        }
        if ((string) config('queue.default') === 'sync') {
            $items[] = 'Trocar fila sync por database ou redis para evitar latência no request.';
        }
        if (!Schema::hasTable('performance_events')) {
            $items[] = 'Criar tabela performance_events para registrar slow queries e comparar hotspots reais.';
        }
        if ((string) config('cache.default') === 'file') {
            $items[] = 'Migrar cache de file para database/redis no cutover para VPS.';
        }

        $items[] = 'Revisar queries grandes detectadas no relatório antes de ativar novos bloqueios.';
        $items[] = 'Executar os relatórios em janela de baixo uso e comparar evolução semanal.';

        return $items;
    }

    private function safeIndexes(string $table): array
    {
        try {
            $driver = config('database.default');
            $connection = config("database.connections.{$driver}.driver");
            if ($connection === 'sqlite') {
                $rows = DB::select("PRAGMA index_list('{$table}')");
                $columns = [];
                foreach ($rows as $row) {
                    $name = Arr::get((array) $row, 'name');
                    if (!$name) {
                        continue;
                    }
                    foreach (DB::select("PRAGMA index_info('{$name}')") as $col) {
                        $columns[] = Arr::get((array) $col, 'name');
                    }
                }

                return array_values(array_unique(array_filter($columns)));
            }

            $rows = DB::select("SHOW INDEX FROM `{$table}`");
            return array_values(array_unique(array_filter(array_map(fn ($row) => Arr::get((array) $row, 'Column_name'), $rows))));
        } catch (Throwable) {
            return [];
        }
    }
}
