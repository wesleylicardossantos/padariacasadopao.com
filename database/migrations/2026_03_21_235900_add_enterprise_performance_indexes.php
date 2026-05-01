<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->indexIfTableExists('conta_recebers', function (Blueprint $table) {
            $table->index(['empresa_id', 'status'], 'cr_empresa_status_idx');
            $table->index(['empresa_id', 'data_vencimento'], 'cr_empresa_vencimento_idx');
            $table->index(['empresa_id', 'filial_id'], 'cr_empresa_filial_idx');
            $table->index(['venda_id'], 'cr_venda_idx');
            $table->index(['venda_caixa_id'], 'cr_venda_caixa_idx');
        });

        $this->indexIfTableExists('conta_pagars', function (Blueprint $table) {
            $table->index(['empresa_id', 'status'], 'cp_empresa_status_idx');
            $table->index(['empresa_id', 'data_vencimento'], 'cp_empresa_vencimento_idx');
            $table->index(['empresa_id', 'filial_id'], 'cp_empresa_filial_idx');
            $table->index(['fornecedor_id'], 'cp_fornecedor_idx');
        });

        $this->indexIfTableExists('vendas', function (Blueprint $table) {
            $table->index(['empresa_id', 'created_at'], 'vendas_empresa_created_idx');
            $table->index(['empresa_id', 'estado'], 'vendas_empresa_estado_idx');
            $table->index(['cliente_id'], 'vendas_cliente_idx');
        });

        $this->indexIfTableExists('venda_caixas', function (Blueprint $table) {
            $table->index(['empresa_id', 'created_at'], 'vc_empresa_created_idx');
            $table->index(['empresa_id', 'status'], 'vc_empresa_status_idx');
        });

        $this->indexIfTableExists('pdv_offline_syncs', function (Blueprint $table) {
            $table->index(['empresa_id', 'status'], 'pdv_sync_empresa_status_idx');
            $table->index(['empresa_id', 'sincronizado_em'], 'pdv_sync_empresa_sync_idx');
            $table->index(['empresa_id', 'ultima_tentativa_em'], 'pdv_sync_empresa_tentativa_idx');
            $table->index(['uuid_local'], 'pdv_sync_uuid_idx');
        });
    }

    public function down(): void
    {
        $this->dropIndexIfExists('conta_recebers', 'cr_empresa_status_idx');
        $this->dropIndexIfExists('conta_recebers', 'cr_empresa_vencimento_idx');
        $this->dropIndexIfExists('conta_recebers', 'cr_empresa_filial_idx');
        $this->dropIndexIfExists('conta_recebers', 'cr_venda_idx');
        $this->dropIndexIfExists('conta_recebers', 'cr_venda_caixa_idx');

        $this->dropIndexIfExists('conta_pagars', 'cp_empresa_status_idx');
        $this->dropIndexIfExists('conta_pagars', 'cp_empresa_vencimento_idx');
        $this->dropIndexIfExists('conta_pagars', 'cp_empresa_filial_idx');
        $this->dropIndexIfExists('conta_pagars', 'cp_fornecedor_idx');

        $this->dropIndexIfExists('vendas', 'vendas_empresa_created_idx');
        $this->dropIndexIfExists('vendas', 'vendas_empresa_estado_idx');
        $this->dropIndexIfExists('vendas', 'vendas_cliente_idx');

        $this->dropIndexIfExists('venda_caixas', 'vc_empresa_created_idx');
        $this->dropIndexIfExists('venda_caixas', 'vc_empresa_status_idx');

        $this->dropIndexIfExists('pdv_offline_syncs', 'pdv_sync_empresa_status_idx');
        $this->dropIndexIfExists('pdv_offline_syncs', 'pdv_sync_empresa_sync_idx');
        $this->dropIndexIfExists('pdv_offline_syncs', 'pdv_sync_empresa_tentativa_idx');
        $this->dropIndexIfExists('pdv_offline_syncs', 'pdv_sync_uuid_idx');
    }

    private function indexIfTableExists(string $table, \Closure $callback): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, $callback);
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($index) {
            try {
                $blueprint->dropIndex($index);
            } catch (Throwable $e) {
                // índice não existe ou já foi removido
            }
        });
    }
};
