<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rh_desligamentos')) {
            Schema::table('rh_desligamentos', function (Blueprint $table) {
                if (!Schema::hasColumn('rh_desligamentos', 'rescisao_id')) {
                    $table->unsignedBigInteger('rescisao_id')->nullable()->after('usuario_id');
                }
            });
        }

        if (Schema::hasTable('rh_portal_funcionarios')) {
            Schema::table('rh_portal_funcionarios', function (Blueprint $table) {
                if (!Schema::hasColumn('rh_portal_funcionarios', 'perfil_id')) {
                    $table->unsignedBigInteger('perfil_id')->nullable()->after('empresa_id');
                }
                if (!Schema::hasColumn('rh_portal_funcionarios', 'permissoes_extras')) {
                    $table->json('permissoes_extras')->nullable()->after('perfil_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rh_portal_funcionarios')) {
            Schema::table('rh_portal_funcionarios', function (Blueprint $table) {
                if (Schema::hasColumn('rh_portal_funcionarios', 'permissoes_extras')) {
                    $table->dropColumn('permissoes_extras');
                }
                if (Schema::hasColumn('rh_portal_funcionarios', 'perfil_id')) {
                    $table->dropColumn('perfil_id');
                }
            });
        }

        if (Schema::hasTable('rh_desligamentos') && Schema::hasColumn('rh_desligamentos', 'rescisao_id')) {
            Schema::table('rh_desligamentos', function (Blueprint $table) {
                $table->dropColumn('rescisao_id');
            });
        }
    }
};
