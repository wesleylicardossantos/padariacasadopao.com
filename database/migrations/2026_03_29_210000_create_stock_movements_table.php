<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_movements')) {
            return;
        }

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('filial_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->enum('type', ['opening_balance', 'in', 'out', 'adjustment']);
            $table->decimal('quantity', 15, 4);
            $table->decimal('balance_after', 15, 4)->default(0);
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->string('source', 50)->nullable()->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable()->index();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->timestamps();

            $table->index(['empresa_id', 'product_id', 'filial_id'], 'stock_movements_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
