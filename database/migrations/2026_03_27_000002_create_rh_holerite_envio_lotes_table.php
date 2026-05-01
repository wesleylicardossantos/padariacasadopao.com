<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rh_holerite_envio_lotes')) {
            return;
        }

        Schema::create('rh_holerite_envio_lotes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(0)->index();
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('ano');
            $table->string('status', 40)->default('na_fila')->index();
            $table->string('queue_connection', 60)->nullable();
            $table->string('queue_name', 60)->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('pendentes')->default(0);
            $table->unsignedInteger('processando')->default(0);
            $table->unsignedInteger('enviados')->default(0);
            $table->unsignedInteger('sem_email')->default(0);
            $table->unsignedInteger('falhas')->default(0);
            $table->string('solicitado_por', 150)->nullable();
            $table->text('observacao')->nullable();
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_holerite_envio_lotes');
    }
};
