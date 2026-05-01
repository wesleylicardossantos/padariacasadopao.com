<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('rh_portal_funcionarios')) {
            return;
        }

        Schema::table('rh_portal_funcionarios', function (Blueprint $table) {
            if (!Schema::hasColumn('rh_portal_funcionarios', 'pode_ver_relatorio_produtos')) {
                $table->boolean('pode_ver_relatorio_produtos')->default(false)->after('ativo');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('rh_portal_funcionarios')) {
            return;
        }

        Schema::table('rh_portal_funcionarios', function (Blueprint $table) {
            if (Schema::hasColumn('rh_portal_funcionarios', 'pode_ver_relatorio_produtos')) {
                $table->dropColumn('pode_ver_relatorio_produtos');
            }
        });
    }
};
