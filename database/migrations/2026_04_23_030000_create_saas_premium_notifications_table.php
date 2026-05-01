<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('saas_premium_notifications')) {
            Schema::create('saas_premium_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->string('channel', 50)->nullable();
                $table->string('level', 30)->nullable();
                $table->string('title', 255)->nullable();
                $table->text('message')->nullable();
                $table->longText('payload_json')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_premium_notifications');
    }
};
