<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venda_caixas', function (Blueprint $table) {
            if (!Schema::hasColumn('venda_caixas', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('venda_caixas', function (Blueprint $table) {
            if (Schema::hasColumn('venda_caixas', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
