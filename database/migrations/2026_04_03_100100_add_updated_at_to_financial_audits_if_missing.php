<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('financial_audits')) {
            return;
        }

        Schema::table('financial_audits', function (Blueprint $table) {
            if (!Schema::hasColumn('financial_audits', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('financial_audits') || !Schema::hasColumn('financial_audits', 'updated_at')) {
            return;
        }

        Schema::table('financial_audits', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
