<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rh_holerite_envios')) {
            return;
        }

        Schema::create('rh_holerite_envios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lote_id')->index();
            $table->unsignedBigInteger('empresa_id')->default(0)->index();
            $table->unsignedBigInteger('apuracao_mensal_id')->nullable()->index();
            $table->unsignedBigInteger('funcionario_id')->index();
            $table->string('email', 150)->nullable();
            $table->string('status', 40)->default('fila')->index();
            $table->unsignedInteger('tentativas')->default(0);
            $table->text('ultima_falha')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('ultima_tentativa_em')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->foreign('lote_id')->references('id')->on('rh_holerite_envio_lotes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_holerite_envios');
    }
};
