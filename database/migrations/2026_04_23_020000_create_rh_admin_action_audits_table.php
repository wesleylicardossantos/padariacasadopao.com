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
                $table->string('acao', 120);
                $table->string('modulo', 80);
                $table->string('referencia_tipo', 120)->nullable();
                $table->unsignedBigInteger('referencia_id')->nullable();
                $table->json('payload_json')->nullable();
                $table->string('ip', 64)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamps();

                $table->index(['empresa_id', 'modulo']);
                $table->index(['usuario_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_admin_action_audits');
    }
};
