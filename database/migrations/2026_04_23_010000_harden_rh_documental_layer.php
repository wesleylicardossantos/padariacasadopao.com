<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rh_documentos')) {
            Schema::table('rh_documentos', function (Blueprint $table) {
                if (!Schema::hasColumn('rh_documentos', 'status')) {
                    $table->string('status', 40)->nullable()->after('origem');
                }
                if (!Schema::hasColumn('rh_documentos', 'hash_conteudo')) {
                    $table->string('hash_conteudo', 120)->nullable()->after('metadata_json');
                }
            });
        }

        if (Schema::hasTable('rh_dossie_eventos')) {
            Schema::table('rh_dossie_eventos', function (Blueprint $table) {
                if (!Schema::hasColumn('rh_dossie_eventos', 'visibilidade_portal')) {
                    $table->boolean('visibilidade_portal')->default(false)->after('data_evento');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rh_dossie_eventos') && Schema::hasColumn('rh_dossie_eventos', 'visibilidade_portal')) {
            Schema::table('rh_dossie_eventos', function (Blueprint $table) {
                $table->dropColumn('visibilidade_portal');
            });
        }

        if (Schema::hasTable('rh_documentos')) {
            Schema::table('rh_documentos', function (Blueprint $table) {
                if (Schema::hasColumn('rh_documentos', 'hash_conteudo')) {
                    $table->dropColumn('hash_conteudo');
                }
                if (Schema::hasColumn('rh_documentos', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
