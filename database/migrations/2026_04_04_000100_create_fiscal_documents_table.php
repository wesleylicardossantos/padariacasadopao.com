<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('fiscal_documents')) {
            return;
        }

        Schema::create('fiscal_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('venda_id')->nullable()->index();
            $table->string('tipo_documento', 30)->index();
            $table->string('numero_referencia', 100)->nullable()->index();
            $table->string('status', 40)->default('prepared')->index();
            $table->json('payload_preparado')->nullable();
            $table->json('retorno_integracao')->nullable();
            $table->string('chave_acesso', 80)->nullable()->index();
            $table->string('motivo', 255)->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('transmitted_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_documents');
    }
};
