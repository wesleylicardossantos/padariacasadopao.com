<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pdv_offline_syncs')) {
            return;
        }

        Schema::create('pdv_offline_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('usuario_id')->nullable()->index();
            $table->uuid('uuid_local');
            $table->string('payload_hash', 64)->nullable();
            $table->string('status', 30)->default('sincronizado')->index();
            $table->unsignedBigInteger('venda_caixa_id')->nullable()->index();
            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();
            $table->text('erro')->nullable();
            $table->timestamp('sincronizado_em')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'uuid_local'], 'pdv_offline_syncs_empresa_uuid_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdv_offline_syncs');
    }
};
