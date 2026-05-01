<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('apuracao_mensals')) {
            return;
        }

        Schema::table('apuracao_mensals', function (Blueprint $table) {
            if (!Schema::hasColumn('apuracao_mensals', 'competencia_id')) {
                $table->unsignedBigInteger('competencia_id')->nullable()->after('empresa_id');
            }
            if (!Schema::hasColumn('apuracao_mensals', 'total_proventos')) {
                $table->decimal('total_proventos', 12, 2)->default(0)->after('valor_final');
            }
            if (!Schema::hasColumn('apuracao_mensals', 'total_descontos')) {
                $table->decimal('total_descontos', 12, 2)->default(0)->after('total_proventos');
            }
            if (!Schema::hasColumn('apuracao_mensals', 'liquido')) {
                $table->decimal('liquido', 12, 2)->default(0)->after('total_descontos');
            }
            if (!Schema::hasColumn('apuracao_mensals', 'base_inss')) {
                $table->decimal('base_inss', 12, 2)->default(0)->after('liquido');
            }
            if (!Schema::hasColumn('apuracao_mensals', 'base_fgts')) {
                $table->decimal('base_fgts', 12, 2)->default(0)->after('base_inss');
            }
            if (!Schema::hasColumn('apuracao_mensals', 'base_irrf')) {
                $table->decimal('base_irrf', 12, 2)->default(0)->after('base_fgts');
            }
            if (!Schema::hasColumn('apuracao_mensals', 'json_calculo')) {
                $table->longText('json_calculo')->nullable()->after('observacao');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('apuracao_mensals')) {
            return;
        }

        Schema::table('apuracao_mensals', function (Blueprint $table) {
            foreach (['competencia_id', 'total_proventos', 'total_descontos', 'liquido', 'base_inss', 'base_fgts', 'base_irrf', 'json_calculo'] as $column) {
                if (Schema::hasColumn('apuracao_mensals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
