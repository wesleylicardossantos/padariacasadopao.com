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
        if (!Schema::hasTable('rh_portal_perfis')) {
            Schema::create('rh_portal_perfis', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->string('nome', 80);
                $table->string('slug', 120)->unique();
                $table->string('descricao', 255)->nullable();
                $table->boolean('ativo')->default(true);
                $table->json('permissoes')->nullable();
                $table->timestamps();
            });
        }

        $now = now();
        foreach (RHPortalPerfil::perfisPadrao() as $perfil) {
            $exists = DB::table('rh_portal_perfis')->where('slug', $perfil['slug'])->exists();
            if (!$exists) {
                DB::table('rh_portal_perfis')->insert([
                    'empresa_id' => null,
                    'nome' => $perfil['nome'],
                    'slug' => $perfil['slug'],
                    'descricao' => $perfil['descricao'],
                    'ativo' => $perfil['ativo'] ? 1 : 0,
                    'permissoes' => json_encode($perfil['permissoes'], JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_portal_perfis');
    }
};
