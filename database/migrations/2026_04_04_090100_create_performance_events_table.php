<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('performance_events')) {
            return;
        }

        Schema::create('performance_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->string('event_type', 50)->index();
            $table->longText('context')->nullable();
            $table->timestamps();
            $table->index(['event_type', 'created_at'], 'performance_events_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_events');
    }
};
