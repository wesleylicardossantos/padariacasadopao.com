<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('rh_portal_perfis')) {
            Schema::create('rh_portal_perfis', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->string('nome', 100);
                $table->string('slug', 120)->nullable();
                $table->string('descricao', 255)->nullable();
                $table->longText('permissoes')->nullable();
                $table->boolean('ativo')->default(true);
                $table->string('escopo', 50)->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('rh_portal_perfis', function (Blueprint $table) {
                if (!Schema::hasColumn('rh_portal_perfis', 'empresa_id')) {
                    $table->unsignedBigInteger('empresa_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('rh_portal_perfis', 'slug')) {
                    $table->string('slug', 120)->nullable()->after('nome');
                }
                if (!Schema::hasColumn('rh_portal_perfis', 'descricao')) {
                    $table->string('descricao', 255)->nullable()->after('slug');
                }
                if (!Schema::hasColumn('rh_portal_perfis', 'permissoes')) {
                    $table->longText('permissoes')->nullable()->after('descricao');
                }
                if (!Schema::hasColumn('rh_portal_perfis', 'ativo')) {
                    $table->boolean('ativo')->default(true)->after('permissoes');
                }
                if (!Schema::hasColumn('rh_portal_perfis', 'escopo')) {
                    $table->string('escopo', 50)->nullable()->after('ativo');
                }
                if (!Schema::hasColumn('rh_portal_perfis', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('rh_portal_perfis', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('rh_document_templates')) {
            Schema::table('rh_document_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('rh_document_templates', 'conteudo_texto')) {
                    $table->longText('conteudo_texto')->nullable()->after('conteudo_html');
                }
                if (!Schema::hasColumn('rh_document_templates', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('versao');
                }
                if (!Schema::hasColumn('rh_document_templates', 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
            });
        }

        if (Schema::hasTable('rh_ferias')) {
            Schema::table('rh_ferias', function (Blueprint $table) {
                if (!Schema::hasColumn('rh_ferias', 'periodo_aquisitivo_inicio')) {
                    $table->date('periodo_aquisitivo_inicio')->nullable()->after('funcionario_id');
                }
                if (!Schema::hasColumn('rh_ferias', 'periodo_aquisitivo_fim')) {
                    $table->date('periodo_aquisitivo_fim')->nullable()->after('periodo_aquisitivo_inicio');
                }
            });
        }
    }

    public function down(): void
    {
        // Patch defensivo para compatibilidade de produção. Sem rollback destrutivo.
    }
};
