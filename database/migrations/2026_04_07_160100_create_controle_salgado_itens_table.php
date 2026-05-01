<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('controle_salgado_itens')) {
            Schema::create('controle_salgado_itens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('controle_salgado_id');
                $table->enum('periodo', ['manha', 'tarde'])->index();
                $table->unsignedSmallInteger('ordem')->default(1);
                $table->string('descricao');
                $table->integer('qtd')->nullable();
                $table->string('termino', 30)->nullable();
                $table->integer('saldo')->nullable();
                $table->timestamps();

                $table->foreign('controle_salgado_id', 'fk_controle_salgado_itens_controle')
                    ->references('id')
                    ->on('controle_salgados')
                    ->onDelete('cascade');

                $table->index(['controle_salgado_id', 'periodo', 'ordem'], 'idx_controle_salgado_itens_lookup');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('controle_salgado_itens');
    }
};
