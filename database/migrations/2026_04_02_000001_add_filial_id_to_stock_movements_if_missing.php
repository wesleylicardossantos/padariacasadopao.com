<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('stock_movements')) {
            return;
        }

        if (! Schema::hasColumn('stock_movements', 'filial_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('filial_id')->nullable()->after('empresa_id');
            });
        }

        $indexes = collect(DB::select('SHOW INDEX FROM stock_movements'))
            ->pluck('Key_name')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (! in_array('stock_movements_filial_id_index', $indexes, true)) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->index('filial_id', 'stock_movements_filial_id_index');
            });
        }

        if (! in_array('stock_movements_scope_idx', $indexes, true)) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->index(['empresa_id', 'product_id', 'filial_id'], 'stock_movements_scope_idx');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('stock_movements') || ! Schema::hasColumn('stock_movements', 'filial_id')) {
            return;
        }

        try {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropIndex('stock_movements_scope_idx');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropIndex('stock_movements_filial_id_index');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('filial_id');
            });
        } catch (\Throwable $e) {
        }
    }
};
