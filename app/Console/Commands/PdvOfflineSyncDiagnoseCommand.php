<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PdvOfflineSyncDiagnoseCommand extends Command
{
    protected $signature = 'pdv:diagnose-offline-sync {empresa_id?}';
    protected $description = 'Diagnostica problemas estruturais e operacionais do sync offline do PDV';

    public function handle(): int
    {
        $table = 'pdv_offline_syncs';
        $empresaId = $this->argument('empresa_id');

        if (!Schema::hasTable($table)) {
            $this->error('Tabela pdv_offline_syncs não existe.');
            return self::FAILURE;
        }

        $this->info('== Estrutura ==');
        $requiredColumns = [
            'id', 'empresa_id', 'usuario_id', 'uuid_local', 'payload_hash', 'status',
            'venda_caixa_id', 'request_payload', 'response_payload', 'erro', 'erro_tipo',
            'mensagem_usuario', 'sincronizado_em', 'tentativas', 'ultima_tentativa_em',
            'processando_desde', 'created_at', 'updated_at',
        ];

        $existingColumns = Schema::getColumnListing($table);
        foreach ($requiredColumns as $column) {
            $this->line(sprintf('%-25s %s', $column, in_array($column, $existingColumns, true) ? 'OK' : 'FALTANDO'));
        }

        $this->newLine();
        $this->info('== Índices ==');
        $indexes = DB::select(
            'SELECT DISTINCT index_name FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? ORDER BY index_name',
            [DB::getDatabaseName(), $table]
        );
        foreach ($indexes as $index) {
            $this->line($index->index_name);
        }

        $this->newLine();
        $this->info('== Duplicados por empresa_id + uuid_local ==');
        $duplicates = DB::table($table)
            ->select('empresa_id', 'uuid_local', DB::raw('COUNT(*) as total'))
            ->when($empresaId, fn ($q) => $q->where('empresa_id', (int) $empresaId))
            ->groupBy('empresa_id', 'uuid_local')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('Nenhum duplicado encontrado.');
        } else {
            $this->table(['empresa_id', 'uuid_local', 'total'], $duplicates->map(fn ($row) => [
                $row->empresa_id,
                $row->uuid_local,
                $row->total,
            ])->all());
        }

        $this->newLine();
        $this->info('== Registros presos em sincronizando há mais de 3 minutos ==');
        $stuck = DB::table($table)
            ->select('id', 'empresa_id', 'uuid_local', 'status', 'tentativas', 'processando_desde', 'updated_at')
            ->when($empresaId, fn ($q) => $q->where('empresa_id', (int) $empresaId))
            ->where('status', 'sincronizando')
            ->where(function ($q) {
                $q->where('processando_desde', '<', now()->subMinutes(3))
                  ->orWhere(function ($sub) {
                      $sub->whereNull('processando_desde')
                          ->where('updated_at', '<', now()->subMinutes(3));
                  });
            })
            ->orderBy('updated_at')
            ->limit(20)
            ->get();

        if ($stuck->isEmpty()) {
            $this->info('Nenhum registro travado encontrado.');
        } else {
            $this->table(['id', 'empresa_id', 'uuid_local', 'status', 'tentativas', 'processando_desde', 'updated_at'], $stuck->map(fn ($row) => [
                $row->id,
                $row->empresa_id,
                $row->uuid_local,
                $row->status,
                $row->tentativas,
                $row->processando_desde,
                $row->updated_at,
            ])->all());
        }

        $this->newLine();
        $this->info('== Resumo por status ==');
        $summary = DB::table($table)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->when($empresaId, fn ($q) => $q->where('empresa_id', (int) $empresaId))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        if ($summary->isEmpty()) {
            $this->warn('Nenhum registro encontrado.');
        } else {
            $this->table(['status', 'total'], $summary->map(fn ($row) => [$row->status, $row->total])->all());
        }

        $this->newLine();
        $this->info('== Recomendações ==');
        $this->line('1. Se houver duplicados, execute primeiro o SQL de limpeza antes de aplicar a unique key.');
        $this->line('2. Se houver muitos registros em sincronizando, revise chamadas concorrentes do endpoint e retries automáticos.');
        $this->line('3. Se faltarem colunas, rode as migrations de hardening antes de subir o código novo.');

        return self::SUCCESS;
    }
}
