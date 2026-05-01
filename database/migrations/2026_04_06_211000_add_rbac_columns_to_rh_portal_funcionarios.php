<?php

use App\Models\RHPortalPerfil;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rh_portal_funcionarios')) {
            return;
        }

        Schema::table('rh_portal_funcionarios', function (Blueprint $table) {
            if (!Schema::hasColumn('rh_portal_funcionarios', 'perfil_id')) {
                $table->unsignedBigInteger('perfil_id')->nullable()->after('funcionario_id');
            }
            if (!Schema::hasColumn('rh_portal_funcionarios', 'permissoes_extras')) {
                $table->json('permissoes_extras')->nullable()->after('perfil_id');
            }
        });

        $perfilComercial = DB::table('rh_portal_perfis')->where('slug', 'portal-comercial')->value('id');
        $perfilBasico = DB::table('rh_portal_perfis')->where('slug', 'portal-basico')->value('id');

        if ($perfilBasico) {
            $registros = DB::table('rh_portal_funcionarios')->select('id', 'pode_ver_relatorio_produtos', 'perfil_id')->get();
            foreach ($registros as $registro) {
                if (!empty($registro->perfil_id)) {
                    continue;
                }

                $perfilId = ((int) ($registro->pode_ver_relatorio_produtos ?? 0) === 1 && $perfilComercial)
                    ? $perfilComercial
                    : $perfilBasico;

                DB::table('rh_portal_funcionarios')
                    ->where('id', $registro->id)
                    ->update([
                        'perfil_id' => $perfilId,
                        'permissoes_extras' => json_encode([], JSON_UNESCAPED_UNICODE),
                    ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('rh_portal_funcionarios')) {
            return;
        }

        Schema::table('rh_portal_funcionarios', function (Blueprint $table) {
            if (Schema::hasColumn('rh_portal_funcionarios', 'permissoes_extras')) {
                $table->dropColumn('permissoes_extras');
            }
            if (Schema::hasColumn('rh_portal_funcionarios', 'perfil_id')) {
                $table->dropColumn('perfil_id');
            }
        });
    }
};
