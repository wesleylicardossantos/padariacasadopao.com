<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('saas_tenant_metrics')) {
            return;
        }

        Schema::create('saas_tenant_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->date('reference_date')->index();
            $table->unsignedInteger('jobs_pending')->default(0);
            $table->unsignedInteger('pdv_pending_sync')->default(0);
            $table->unsignedTinyInteger('scale_score')->default(100);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['empresa_id', 'reference_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_tenant_metrics');
    }
};
