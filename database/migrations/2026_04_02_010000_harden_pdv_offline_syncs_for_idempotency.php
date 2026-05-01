<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $table = 'pdv_offline_syncs';
    private string $uniqueName = 'pdv_offline_syncs_empresa_uuid_unique';

    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            if (!Schema::hasColumn($this->table, 'payload_hash')) {
                $table->string('payload_hash', 64)->nullable()->after('uuid_local');
            }

            if (!Schema::hasColumn($this->table, 'venda_caixa_id')) {
                $table->unsignedBigInteger('venda_caixa_id')->nullable()->after('status');
            }

            if (!Schema::hasColumn($this->table, 'request_payload')) {
                $table->longText('request_payload')->nullable()->after('venda_caixa_id');
            }

            if (!Schema::hasColumn($this->table, 'response_payload')) {
                $table->longText('response_payload')->nullable()->after('request_payload');
            }

            if (!Schema::hasColumn($this->table, 'erro')) {
                $table->text('erro')->nullable()->after('response_payload');
            }

            if (!Schema::hasColumn($this->table, 'sincronizado_em')) {
                $table->timestamp('sincronizado_em')->nullable()->after('erro');
            }

            if (!Schema::hasColumn($this->table, 'tentativas')) {
                $table->unsignedInteger('tentativas')->default(0)->after('sincronizado_em');
            }

            if (!Schema::hasColumn($this->table, 'ultima_tentativa_em')) {
                $table->timestamp('ultima_tentativa_em')->nullable()->after('tentativas');
            }

            if (!Schema::hasColumn($this->table, 'erro_tipo')) {
                $table->string('erro_tipo', 60)->nullable()->after('erro');
            }

            if (!Schema::hasColumn($this->table, 'mensagem_usuario')) {
                $table->string('mensagem_usuario', 255)->nullable()->after('erro_tipo');
            }

            if (!Schema::hasColumn($this->table, 'processando_desde')) {
                $table->timestamp('processando_desde')->nullable()->after('ultima_tentativa_em');
            }
        });

        $this->ensureIndex('empresa_id');
        $this->ensureIndex('status');
        $this->ensureIndex('created_at');
        $this->ensureIndex('venda_caixa_id');
        $this->ensureIndex(['empresa_id', 'status'], 'pdv_offline_syncs_empresa_status_index');

        if (!$this->hasIndex($this->uniqueName) && !$this->hasDuplicateEmpresaUuid()) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unique(['empresa_id', 'uuid_local'], $this->uniqueName);
            });
        }
    }

    public function down(): void
    {
        // migration de hardening; manter rollback conservador
    }

    private function hasDuplicateEmpresaUuid(): bool
    {
        $row = DB::table($this->table)
            ->selectRaw('COUNT(*) AS total')
            ->fromSub(function ($query) {
                $query->from($this->table)
                    ->select('empresa_id', 'uuid_local')
                    ->groupBy('empresa_id', 'uuid_local')
                    ->havingRaw('COUNT(*) > 1');
            }, 'duplicados')
            ->first();

        return ((int) ($row->total ?? 0)) > 0;
    }

    private function ensureIndex(string|array $columns, ?string $name = null): void
    {
        $indexName = $name ?: $this->defaultIndexName($columns);
        if ($this->hasIndex($indexName)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function defaultIndexName(string|array $columns): string
    {
        if (is_array($columns)) {
            return $this->table . '_' . implode('_', $columns) . '_index';
        }

        return $this->table . '_' . $columns . '_index';
    }

    private function hasIndex(string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $result = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $this->table, $indexName]
        );

        return ((int) ($result->total ?? 0)) > 0;
    }
};
