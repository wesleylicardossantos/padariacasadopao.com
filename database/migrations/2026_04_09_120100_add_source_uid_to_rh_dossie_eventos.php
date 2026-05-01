<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('rh_dossie_eventos')) {
            return;
        }

        Schema::table('rh_dossie_eventos', function (Blueprint $table) {
            if (!Schema::hasColumn('rh_dossie_eventos', 'source_uid')) {
                $table->string('source_uid', 180)->nullable()->after('origem');
            }
        });

        Schema::table('rh_dossie_eventos', function (Blueprint $table) {
            try {
                $table->unique(['empresa_id', 'funcionario_id', 'source_uid'], 'uk_rh_dossie_eventos_source_uid');
            } catch (\Throwable $e) {
                // índice já existente em bases parcialmente migradas.
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('rh_dossie_eventos')) {
            return;
        }

        Schema::table('rh_dossie_eventos', function (Blueprint $table) {
            try {
                $table->dropUnique('uk_rh_dossie_eventos_source_uid');
            } catch (\Throwable $e) {
            }

            if (Schema::hasColumn('rh_dossie_eventos', 'source_uid')) {
                $table->dropColumn('source_uid');
            }
        });
    }
};
