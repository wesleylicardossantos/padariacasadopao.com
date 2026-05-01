<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rh_acl_papeis')) {
            Schema::create('rh_acl_papeis', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->string('nome', 120);
                $table->string('slug', 140)->nullable()->index();
                $table->text('descricao')->nullable();
                $table->boolean('ativo')->default(true)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_acl_permissoes')) {
            Schema::create('rh_acl_permissoes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('chave', 160)->unique();
                $table->string('nome', 160)->nullable();
                $table->string('modulo', 80)->default('rh')->index();
                $table->text('descricao')->nullable();
                $table->boolean('ativo')->default(true)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_acl_papel_permissoes')) {
            Schema::create('rh_acl_papel_permissoes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('papel_id')->index();
                $table->unsignedBigInteger('permissao_id')->index();
                $table->timestamps();
                $table->unique(['papel_id', 'permissao_id'], 'rh_acl_papel_perm_unique');
            });
        }

        if (!Schema::hasTable('rh_acl_papel_usuarios')) {
            Schema::create('rh_acl_papel_usuarios', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('funcionario_id')->nullable()->index();
                $table->unsignedBigInteger('papel_id')->index();
                $table->timestamps();
            });
        }

        $this->seedPermissoesBase();
    }

    public function down(): void
    {
        // Não destrutivo por padrão. Remoção manual apenas com backup validado.
    }

    private function seedPermissoesBase(): void
    {
        if (!Schema::hasTable('rh_acl_permissoes')) {
            return;
        }

        $permissoes = [
            'rh.dashboard.executivo' => 'Dashboard HR Executivo',
            'rh.funcionarios.visualizar' => 'Visualizar funcionários',
            'rh.funcionarios.editar' => 'Editar funcionários',
            'rh.folha.processar' => 'Processar folha',
            'rh.holerites.visualizar' => 'Visualizar holerites',
            'rh.dossie.visualizar' => 'Visualizar dossiê',
            'rh.portal.gerenciar' => 'Gerenciar portal do funcionário',
            'rh.produtos.relatorio_portal' => 'Relatório de produtos no portal',
        ];

        foreach ($permissoes as $chave => $nome) {
            DB::table('rh_acl_permissoes')->updateOrInsert(
                ['chave' => $chave],
                ['nome' => $nome, 'modulo' => 'rh', 'ativo' => 1, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
};
