<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('commercial_audits')) {
            return;
        }

        Schema::create('commercial_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('usuario_id')->nullable()->index();
            $table->string('entidade', 100)->index();
            $table->unsignedBigInteger('entidade_id')->nullable()->index();
            $table->string('acao', 50)->index();
            $table->json('antes')->nullable();
            $table->json('depois')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_audits');
    }
};
