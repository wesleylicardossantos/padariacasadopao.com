<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pdv_offline_syncs', function (Blueprint $table) {
            $table->index(['empresa_id', 'status', 'tentativas', 'updated_at'], 'pdv_syncs_retry_cutoff_idx');
            $table->index(['empresa_id', 'sincronizado_em'], 'pdv_syncs_empresa_sync_time_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['empresa_id', 'filial_id', 'product_id', 'occurred_at'], 'stock_movements_scope_occurred_idx');
        });

        Schema::table('conta_recebers', function (Blueprint $table) {
            $table->index(['empresa_id', 'filial_id', 'status', 'data_recebimento'], 'cr_empresa_filial_status_receb_idx');
        });

        Schema::table('conta_pagars', function (Blueprint $table) {
            $table->index(['empresa_id', 'filial_id', 'status', 'data_pagamento'], 'cp_empresa_filial_status_pag_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pdv_offline_syncs', function (Blueprint $table) {
            $table->dropIndex('pdv_syncs_retry_cutoff_idx');
            $table->dropIndex('pdv_syncs_empresa_sync_time_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_scope_occurred_idx');
        });

        Schema::table('conta_recebers', function (Blueprint $table) {
            $table->dropIndex('cr_empresa_filial_status_receb_idx');
        });

        Schema::table('conta_pagars', function (Blueprint $table) {
            $table->dropIndex('cp_empresa_filial_status_pag_idx');
        });
    }
};
