<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('rh_acl_permissoes')) {
            Schema::create('rh_acl_permissoes', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 120)->unique();
                $table->string('nome', 120);
                $table->string('modulo', 40)->default('rh');
                $table->string('descricao', 255)->nullable();
                $table->boolean('ativo')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_acl_papeis')) {
            Schema::create('rh_acl_papeis', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->string('nome', 120);
                $table->string('slug', 120)->unique();
                $table->string('descricao', 255)->nullable();
                $table->boolean('ativo')->default(true);
                $table->boolean('is_admin')->default(false);
                $table->timestamps();
                $table->index(['empresa_id', 'ativo'], 'idx_rh_acl_papeis_empresa_ativo');
            });
        }

        if (!Schema::hasTable('rh_acl_papel_permissoes')) {
            Schema::create('rh_acl_papel_permissoes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('papel_id');
                $table->unsignedBigInteger('permissao_id');
                $table->timestamps();
                $table->unique(['papel_id', 'permissao_id'], 'uk_rh_acl_papel_permissoes');
                $table->index(['permissao_id'], 'idx_rh_acl_papel_permissoes_perm');
            });
        }

        if (!Schema::hasTable('rh_acl_papel_usuarios')) {
            Schema::create('rh_acl_papel_usuarios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('papel_id');
                $table->unsignedBigInteger('usuario_id');
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->boolean('ativo')->default(true);
                $table->timestamps();
                $table->unique(['papel_id', 'usuario_id'], 'uk_rh_acl_papel_usuarios');
                $table->index(['usuario_id', 'empresa_id', 'ativo'], 'idx_rh_acl_papel_usuarios_user_emp');
            });
        }

        if (Schema::hasTable('rh_acl_permissoes') && DB::table('rh_acl_permissoes')->count() === 0) {
            $now = now();
            DB::table('rh_acl_permissoes')->insert([
                ['codigo' => 'rh.dashboard.visualizar', 'nome' => 'Visualizar Dashboard RH', 'descricao' => 'Acesso aos dashboards executivos e operacionais de RH.', 'modulo' => 'rh', 'ativo' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['codigo' => 'rh.dashboard.executivo', 'nome' => 'Visualizar Dashboard Executivo RH', 'descricao' => 'Acesso ao dashboard executivo com dados reais.', 'modulo' => 'rh', 'ativo' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['codigo' => 'rh.dossie.visualizar', 'nome' => 'Visualizar Dossiê', 'descricao' => 'Consulta do dossiê do funcionário.', 'modulo' => 'rh', 'ativo' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['codigo' => 'rh.dossie.documentos.gerenciar', 'nome' => 'Gerenciar Documentos do Dossiê', 'descricao' => 'Upload e manutenção de documentos do dossiê.', 'modulo' => 'rh', 'ativo' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['codigo' => 'rh.dossie.documentos.excluir', 'nome' => 'Excluir Documentos do Dossiê', 'descricao' => 'Exclusão de documentos do dossiê.', 'modulo' => 'rh', 'ativo' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['codigo' => 'rh.dossie.eventos.gerenciar', 'nome' => 'Gerenciar Eventos do Dossiê', 'descricao' => 'Cadastro manual e manutenção de eventos do dossiê.', 'modulo' => 'rh', 'ativo' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['codigo' => 'rh.dossie.automacao.executar', 'nome' => 'Executar Automação do Dossiê', 'descricao' => 'Sincronização automática do dossiê.', 'modulo' => 'rh', 'ativo' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['codigo' => 'rh.acl.gerenciar', 'nome' => 'Gerenciar RBAC RH', 'descricao' => 'Administração de papéis e permissões do módulo RH.', 'modulo' => 'rh', 'ativo' => 1, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_acl_papel_usuarios');
        Schema::dropIfExists('rh_acl_papel_permissoes');
        Schema::dropIfExists('rh_acl_papeis');
        Schema::dropIfExists('rh_acl_permissoes');
    }
};
