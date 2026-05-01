<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rh_portal_perfis')) {
            Schema::create('rh_portal_perfis', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->string('nome', 120);
                $table->string('slug', 140)->nullable()->index();
                $table->text('descricao')->nullable();
                $table->json('permissoes')->nullable();
                $table->boolean('ativo')->default(true)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_portal_funcionarios')) {
            Schema::create('rh_portal_funcionarios', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->unsignedBigInteger('funcionario_id')->index();
                $table->unsignedBigInteger('perfil_id')->nullable()->index();
                $table->string('email', 191)->nullable()->index();
                $table->string('senha', 191)->nullable();
                $table->boolean('ativo')->default(true)->index();
                $table->boolean('pode_ver_produtos')->default(false);
                $table->rememberToken();
                $table->timestamp('ultimo_acesso_em')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_dossies')) {
            Schema::create('rh_dossies', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->unsignedBigInteger('funcionario_id')->index();
                $table->string('codigo', 80)->nullable()->index();
                $table->string('status', 40)->default('ativo')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_dossie_eventos')) {
            Schema::create('rh_dossie_eventos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->unsignedBigInteger('dossie_id')->nullable()->index();
                $table->unsignedBigInteger('funcionario_id')->nullable()->index();
                $table->string('tipo', 80)->index();
                $table->string('titulo', 191)->nullable();
                $table->text('descricao')->nullable();
                $table->string('source_uid', 191)->nullable()->index();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_folha_itens')) {
            Schema::create('rh_folha_itens', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->unsignedBigInteger('competencia_id')->nullable()->index();
                $table->unsignedBigInteger('apuracao_id')->nullable()->index();
                $table->unsignedBigInteger('funcionario_id')->index();
                $table->unsignedBigInteger('evento_id')->nullable()->index();
                $table->string('codigo', 60)->nullable()->index();
                $table->string('nome', 191)->nullable();
                $table->string('tipo', 40)->nullable();
                $table->string('condicao', 40)->nullable();
                $table->decimal('referencia', 15, 4)->default(0);
                $table->decimal('valor', 15, 2)->default(0);
                $table->string('origem', 60)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        } else {
            $this->ensureColumn('rh_folha_itens', 'empresa_id', fn (Blueprint $table) => $table->unsignedBigInteger('empresa_id')->nullable()->index());
            $this->ensureColumn('rh_folha_itens', 'competencia_id', fn (Blueprint $table) => $table->unsignedBigInteger('competencia_id')->nullable()->index());
            $this->ensureColumn('rh_folha_itens', 'apuracao_id', fn (Blueprint $table) => $table->unsignedBigInteger('apuracao_id')->nullable()->index());
            $this->ensureColumn('rh_folha_itens', 'funcionario_id', fn (Blueprint $table) => $table->unsignedBigInteger('funcionario_id')->nullable()->index());
            $this->ensureColumn('rh_folha_itens', 'evento_id', fn (Blueprint $table) => $table->unsignedBigInteger('evento_id')->nullable()->index());
            $this->ensureColumn('rh_folha_itens', 'origem', fn (Blueprint $table) => $table->string('origem', 60)->nullable());
            $this->ensureColumn('rh_folha_itens', 'metadata', fn (Blueprint $table) => $table->json('metadata')->nullable());
        }

        $this->seedPerfilBase();
    }

    public function down(): void
    {
        // Migration de reconciliação final: não remove tabelas/colunas em produção para evitar perda de dados.
    }

    private function ensureColumn(string $tableName, string $column, callable $definition): void
    {
        if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, $column)) {
            Schema::table($tableName, function (Blueprint $table) use ($definition) {
                $definition($table);
            });
        }
    }

    private function seedPerfilBase(): void
    {
        if (!Schema::hasTable('rh_portal_perfis')) {
            return;
        }

        $exists = DB::table('rh_portal_perfis')->where('slug', 'funcionario_padrao')->exists();
        if (!$exists) {
            DB::table('rh_portal_perfis')->insert([
                'empresa_id' => null,
                'nome' => 'Funcionário padrão',
                'slug' => 'funcionario_padrao',
                'descricao' => 'Perfil base do portal do funcionário criado na reconciliação final da refatoração.',
                'permissoes' => json_encode(['portal.dashboard', 'portal.holerites', 'portal.dossie']),
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
