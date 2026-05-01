<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('saas_usage_snapshots')) {
            return;
        }

        Schema::create('saas_usage_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->date('reference_date')->index();
            $table->json('usage_payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_usage_snapshots');
    }
};
