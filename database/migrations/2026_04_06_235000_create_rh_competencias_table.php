<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rh_competencias')) {
            return;
        }

        Schema::create('rh_competencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empresa_id')->default(0);
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('ano');
            $table->string('status', 20)->default('aberta');
            $table->timestamp('processado_em')->nullable();
            $table->timestamp('fechado_em')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('observacao', 255)->nullable();
            $table->timestamps();
            $table->unique(['empresa_id', 'mes', 'ano'], 'rh_competencias_empresa_mes_ano_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_competencias');
    }
};
