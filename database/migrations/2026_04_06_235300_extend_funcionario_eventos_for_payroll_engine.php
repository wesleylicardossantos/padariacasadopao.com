<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('funcionario_eventos')) {
            return;
        }

        Schema::table('funcionario_eventos', function (Blueprint $table) {
            if (!Schema::hasColumn('funcionario_eventos', 'referencia')) {
                $table->decimal('referencia', 10, 2)->nullable()->after('valor');
            }
            if (!Schema::hasColumn('funcionario_eventos', 'tipo_calculo')) {
                $table->string('tipo_calculo', 20)->default('fixo')->after('referencia');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('funcionario_eventos')) {
            return;
        }

        Schema::table('funcionario_eventos', function (Blueprint $table) {
            foreach (['referencia', 'tipo_calculo'] as $column) {
                if (Schema::hasColumn('funcionario_eventos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
