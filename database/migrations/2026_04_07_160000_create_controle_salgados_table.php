<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('controle_salgados')) {
            Schema::create('controle_salgados', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->date('data')->index();
                $table->string('dia', 60)->nullable();
                $table->text('observacoes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['empresa_id', 'data'], 'idx_controle_salgados_empresa_data');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('controle_salgados');
    }
};
