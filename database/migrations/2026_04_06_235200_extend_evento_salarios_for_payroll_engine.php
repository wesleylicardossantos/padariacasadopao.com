<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('evento_salarios')) {
            return;
        }

        Schema::table('evento_salarios', function (Blueprint $table) {
            if (!Schema::hasColumn('evento_salarios', 'codigo')) {
                $table->string('codigo', 50)->nullable()->after('nome');
            }
            if (!Schema::hasColumn('evento_salarios', 'ordem_calculo')) {
                $table->integer('ordem_calculo')->default(0)->after('tipo_valor');
            }
            if (!Schema::hasColumn('evento_salarios', 'formula')) {
                $table->text('formula')->nullable()->after('ordem_calculo');
            }
            if (!Schema::hasColumn('evento_salarios', 'padrao_sistema')) {
                $table->boolean('padrao_sistema')->default(0)->after('formula');
            }
            if (!Schema::hasColumn('evento_salarios', 'incidencia_inss')) {
                $table->boolean('incidencia_inss')->default(1)->after('padrao_sistema');
            }
            if (!Schema::hasColumn('evento_salarios', 'incidencia_fgts')) {
                $table->boolean('incidencia_fgts')->default(1)->after('incidencia_inss');
            }
            if (!Schema::hasColumn('evento_salarios', 'incidencia_irrf')) {
                $table->boolean('incidencia_irrf')->default(1)->after('incidencia_fgts');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('evento_salarios')) {
            return;
        }

        Schema::table('evento_salarios', function (Blueprint $table) {
            foreach (['codigo', 'ordem_calculo', 'formula', 'padrao_sistema', 'incidencia_inss', 'incidencia_fgts', 'incidencia_irrf'] as $column) {
                if (Schema::hasColumn('evento_salarios', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
