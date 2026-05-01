<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pdv_offline_syncs')) {
            return;
        }

        Schema::table('pdv_offline_syncs', function (Blueprint $table) {
            if (!Schema::hasColumn('pdv_offline_syncs', 'tentativas')) {
                $table->unsignedInteger('tentativas')->default(0)->after('sincronizado_em');
            }

            if (!Schema::hasColumn('pdv_offline_syncs', 'ultima_tentativa_em')) {
                $table->timestamp('ultima_tentativa_em')->nullable()->after('tentativas');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pdv_offline_syncs')) {
            return;
        }

        Schema::table('pdv_offline_syncs', function (Blueprint $table) {
            $drops = [];

            if (Schema::hasColumn('pdv_offline_syncs', 'ultima_tentativa_em')) {
                $drops[] = 'ultima_tentativa_em';
            }

            if (Schema::hasColumn('pdv_offline_syncs', 'tentativas')) {
                $drops[] = 'tentativas';
            }

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
