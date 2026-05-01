<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('rh_dossie_eventos')) {
            return;
        }

        Schema::create('rh_dossie_eventos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('dossie_id')->index();
            $table->unsignedBigInteger('funcionario_id')->index();
            $table->string('categoria', 40)->index();
            $table->string('titulo', 120);
            $table->text('descricao')->nullable();
            $table->string('origem', 40)->default('manual')->index();
            $table->unsignedBigInteger('origem_id')->nullable()->index();
            $table->date('data_evento')->index();
            $table->boolean('visibilidade_portal')->default(false)->index();
            $table->json('payload_json')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable()->index();
            $table->timestamps();

            $table->index(['empresa_id', 'funcionario_id', 'categoria'], 'idx_rh_dossie_eventos_emp_func_cat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_dossie_eventos');
    }
};
