<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rh_competencias')) {
            return;
        }

        Schema::table('rh_competencias', function (Blueprint $table) {
            if (!Schema::hasColumn('rh_competencias', 'processado_em')) {
                $table->timestamp('processado_em')->nullable()->after('status');
            }
            if (!Schema::hasColumn('rh_competencias', 'fechado_em')) {
                $table->timestamp('fechado_em')->nullable()->after('processado_em');
            }
            if (!Schema::hasColumn('rh_competencias', 'usuario_id')) {
                $table->unsignedBigInteger('usuario_id')->nullable()->after('fechado_em');
            }
            if (!Schema::hasColumn('rh_competencias', 'observacao')) {
                $table->string('observacao', 255)->nullable()->after('usuario_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('rh_competencias')) {
            return;
        }

        Schema::table('rh_competencias', function (Blueprint $table) {
            foreach (['processado_em', 'fechado_em', 'usuario_id', 'observacao'] as $column) {
                if (Schema::hasColumn('rh_competencias', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
