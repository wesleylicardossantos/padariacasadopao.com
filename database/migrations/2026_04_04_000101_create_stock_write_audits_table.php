<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_write_audits')) {
            return;
        }

        Schema::create('stock_write_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->unsignedBigInteger('filial_id')->nullable()->index();
            $table->unsignedBigInteger('produto_id')->nullable()->index();
            $table->string('event', 50)->index();
            $table->unsignedBigInteger('legacy_stock_id')->nullable()->index();
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->string('guard_source', 100)->nullable()->index();
            $table->boolean('guard_allowed')->default(false)->index();
            $table->unsignedBigInteger('performed_by')->nullable()->index();
            $table->string('request_path', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_write_audits');
    }
};
