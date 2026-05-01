<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('rh_documentos')) {
            return;
        }

        Schema::table('rh_documentos', function (Blueprint $table) {
            if (!Schema::hasColumn('rh_documentos', 'categoria')) {
                $table->string('categoria', 60)->nullable()->after('tipo')->index();
            }
            if (!Schema::hasColumn('rh_documentos', 'origem')) {
                $table->string('origem', 40)->nullable()->after('observacao')->index();
            }
            if (!Schema::hasColumn('rh_documentos', 'metadata_json')) {
                $table->json('metadata_json')->nullable()->after('origem');
            }
            if (!Schema::hasColumn('rh_documentos', 'usuario_id')) {
                $table->unsignedBigInteger('usuario_id')->nullable()->after('metadata_json')->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('rh_documentos')) {
            return;
        }

        Schema::table('rh_documentos', function (Blueprint $table) {
            foreach (['categoria', 'origem', 'metadata_json', 'usuario_id'] as $column) {
                if (Schema::hasColumn('rh_documentos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
