<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('funcionarios_dependentes')) {
            return;
        }

        Schema::create('funcionarios_dependentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('funcionario_id')->index();

            $table->string('nome', 150);
            $table->date('data_nascimento')->nullable();
            $table->string('local_nascimento', 150)->nullable();
            $table->string('parentesco', 60)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funcionarios_dependentes');
    }
};
