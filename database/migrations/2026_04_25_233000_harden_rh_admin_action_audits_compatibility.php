<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rh_admin_action_audits')) {
            Schema::create('rh_admin_action_audits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('modulo', 120)->nullable();
                $table->string('acao', 120)->nullable();
                $table->string('alvo_tipo', 160)->nullable();
                $table->unsignedBigInteger('alvo_id')->nullable();
                $table->string('referencia_tipo', 120)->nullable();
                $table->unsignedBigInteger('referencia_id')->nullable();
                $table->longText('payload_json')->nullable();
                $table->string('ip', 64)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table('rh_admin_action_audits', function (Blueprint $table) {
            if (!Schema::hasColumn('rh_admin_action_audits', 'empresa_id')) {
                $table->unsignedBigInteger('empresa_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'usuario_id')) {
                $table->unsignedBigInteger('usuario_id')->nullable()->after('empresa_id');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'modulo')) {
                $table->string('modulo', 120)->nullable()->after('usuario_id');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'acao')) {
                $table->string('acao', 120)->nullable()->after('modulo');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'alvo_tipo')) {
                $table->string('alvo_tipo', 160)->nullable()->after('acao');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'alvo_id')) {
                $table->unsignedBigInteger('alvo_id')->nullable()->after('alvo_tipo');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'referencia_tipo')) {
                $table->string('referencia_tipo', 120)->nullable()->after('alvo_id');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'referencia_id')) {
                $table->unsignedBigInteger('referencia_id')->nullable()->after('referencia_tipo');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'payload_json')) {
                $table->longText('payload_json')->nullable()->after('referencia_id');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'ip')) {
                $table->string('ip', 64)->nullable()->after('payload_json');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip');
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('rh_admin_action_audits', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Não remove colunas/tabelas em produção para evitar perda de auditoria.
    }
};
