<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pdv_offline_syncs')) {
            return;
        }

        Schema::table('pdv_offline_syncs', function (Blueprint $table) {
            if (!Schema::hasColumn('pdv_offline_syncs', 'response_payload')) {
                $table->longText('response_payload')->nullable();
            }
        });

        DB::table('pdv_offline_syncs')
            ->where('status', 'erro')
            ->update(['status' => 'erro_recuperavel']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('pdv_offline_syncs')) {
            return;
        }

        DB::table('pdv_offline_syncs')
            ->where('status', 'erro_recuperavel')
            ->update(['status' => 'erro']);
    }
};
