<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('rh_dossies')) {
            return;
        }

        Schema::create('rh_dossies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('funcionario_id')->index();
            $table->string('status', 30)->default('ativo')->index();
            $table->timestamp('ultima_atualizacao_em')->nullable()->index();
            $table->text('observacoes_internas')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'funcionario_id'], 'uk_rh_dossies_empresa_funcionario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_dossies');
    }
};
