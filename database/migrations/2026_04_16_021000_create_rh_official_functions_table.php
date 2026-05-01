<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('rh_official_functions')) {
            Schema::create('rh_official_functions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codigo', 20)->unique();
                $table->string('descricao', 255);
                $table->string('descricao_normalizada', 255)->nullable()->index();
                $table->string('cbo_codigo', 10)->nullable()->index();
                $table->boolean('ativo')->default(true);
                $table->string('fonte', 120)->nullable();
                $table->string('fonte_url', 255)->nullable();
                $table->timestamp('fonte_atualizada_em')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_official_functions');
    }
};
