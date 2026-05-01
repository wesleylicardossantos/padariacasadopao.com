<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'branding_logo_path')) {
                $table->string('branding_logo_path')->nullable()->after('hash');
            }
            if (!Schema::hasColumn('empresas', 'branding_background_path')) {
                $table->string('branding_background_path')->nullable()->after('branding_logo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            if (Schema::hasColumn('empresas', 'branding_background_path')) {
                $table->dropColumn('branding_background_path');
            }
            if (Schema::hasColumn('empresas', 'branding_logo_path')) {
                $table->dropColumn('branding_logo_path');
            }
        });
    }
};
