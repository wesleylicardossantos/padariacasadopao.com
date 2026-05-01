<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('saas_tenant_settings')) {
            return;
        }

        Schema::create('saas_tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->string('setting_key')->index();
            $table->json('setting_value')->nullable();
            $table->timestamps();
            $table->unique(['empresa_id', 'setting_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_tenant_settings');
    }
};
