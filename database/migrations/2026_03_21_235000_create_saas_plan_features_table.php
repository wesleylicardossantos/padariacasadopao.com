<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('saas_plan_features')) {
            return;
        }

        Schema::create('saas_plan_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plano_id')->nullable()->index();
            $table->string('feature_key')->index();
            $table->string('feature_label')->nullable();
            $table->integer('limit_value')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_plan_features');
    }
};
