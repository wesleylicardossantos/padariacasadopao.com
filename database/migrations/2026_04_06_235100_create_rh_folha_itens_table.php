<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rh_folha_itens')) {
            return;
        }

        Schema::create('rh_folha_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empresa_id')->default(0);
            $table->unsignedBigInteger('competencia_id')->nullable();
            $table->unsignedBigInteger('apuracao_id')->nullable();
            $table->unsignedBigInteger('funcionario_id')->nullable();
            $table->unsignedBigInteger('evento_id')->nullable();
            $table->string('codigo', 50)->nullable();
            $table->string('nome', 120);
            $table->string('tipo', 40)->nullable();
            $table->string('condicao', 20)->nullable();
            $table->decimal('referencia', 10, 2)->nullable();
            $table->decimal('valor', 12, 2)->default(0);
            $table->string('origem', 40)->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'competencia_id'], 'rh_folha_itens_empresa_competencia_idx');
            $table->index(['funcionario_id', 'competencia_id'], 'rh_folha_itens_func_competencia_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_folha_itens');
    }
};
