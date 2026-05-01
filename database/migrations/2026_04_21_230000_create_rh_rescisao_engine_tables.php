<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rh_parametros_fiscais')) {
            Schema::create('rh_parametros_fiscais', function (Blueprint $table) {
                $table->id();
                $table->string('competencia', 7)->unique();
                $table->json('inss_faixas_json');
                $table->decimal('inss_teto', 10, 2)->default(0);
                $table->json('irrf_faixas_json');
                $table->decimal('irrf_dependente', 10, 2)->default(0);
                $table->decimal('irrf_desconto_simplificado', 10, 2)->default(0);
                $table->decimal('fgts_percentual', 5, 2)->default(8.00);
                $table->decimal('fgts_multa_percentual', 5, 2)->default(40.00);
                $table->boolean('ativo')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_rescisoes')) {
            Schema::create('rh_rescisoes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->unsignedBigInteger('funcionario_id')->index();
                $table->unsignedBigInteger('desligamento_id')->nullable()->index();
                $table->date('data_admissao')->nullable();
                $table->date('data_rescisao')->index();
                $table->string('motivo', 255)->nullable();
                $table->string('tipo_aviso', 40)->nullable();
                $table->unsignedInteger('dependentes_irrf')->default(0);
                $table->decimal('descontos_extras', 12, 2)->default(0);
                $table->decimal('saldo_salario', 12, 2)->default(0);
                $table->decimal('ferias_vencidas', 12, 2)->default(0);
                $table->decimal('ferias_proporcionais', 12, 2)->default(0);
                $table->decimal('terco_ferias', 12, 2)->default(0);
                $table->decimal('decimo_terceiro', 12, 2)->default(0);
                $table->decimal('aviso_previo', 12, 2)->default(0);
                $table->decimal('fgts_base', 12, 2)->default(0);
                $table->decimal('fgts_deposito', 12, 2)->default(0);
                $table->decimal('inss', 12, 2)->default(0);
                $table->decimal('irrf', 12, 2)->default(0);
                $table->decimal('fgts_multa', 12, 2)->default(0);
                $table->decimal('total_bruto', 12, 2)->default(0);
                $table->decimal('total_descontos', 12, 2)->default(0);
                $table->decimal('total_liquido', 12, 2)->default(0);
                $table->text('observacoes')->nullable();
                $table->string('status', 40)->default('processada');
                $table->json('documentos_json')->nullable();
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rh_rescisao_itens')) {
            Schema::create('rh_rescisao_itens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rescisao_id')->index();
                $table->string('codigo', 50);
                $table->string('descricao', 150);
                $table->enum('tipo', ['provento', 'desconto']);
                $table->decimal('referencia', 10, 4)->nullable();
                $table->decimal('valor', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('rh_desligamentos')) {
            Schema::table('rh_desligamentos', function (Blueprint $table) {
                if (!Schema::hasColumn('rh_desligamentos', 'rescisao_id')) {
                    $table->unsignedBigInteger('rescisao_id')->nullable()->after('usuario_id');
                }
            });
        }

        if (Schema::hasTable('rh_portal_perfis')) {
            DB::table('rh_portal_perfis')->get()->each(function ($perfil) {
                $permissoes = json_decode((string) ($perfil->permissoes ?? '[]'), true) ?: [];
                foreach (['documentos.rescisao.visualizar', 'documentos.visualizar'] as $permissao) {
                    if (!in_array($permissao, $permissoes, true)) {
                        $permissoes[] = $permissao;
                    }
                }
                DB::table('rh_portal_perfis')->where('id', $perfil->id)->update(['permissoes' => json_encode(array_values(array_unique($permissoes)))]);
            });
        }

        DB::table('rh_parametros_fiscais')->updateOrInsert(
            ['competencia' => '2026-01'],
            [
                'inss_faixas_json' => json_encode([
                    ['ate' => 1621.00, 'aliquota' => 7.5],
                    ['ate' => 2902.84, 'aliquota' => 9.0],
                    ['ate' => 4354.27, 'aliquota' => 12.0],
                    ['ate' => 8475.55, 'aliquota' => 14.0],
                ]),
                'inss_teto' => 8475.55,
                'irrf_faixas_json' => json_encode([
                    ['ate' => 2428.80, 'aliquota' => 0.0, 'deducao' => 0.00],
                    ['ate' => 2826.65, 'aliquota' => 7.5, 'deducao' => 182.16],
                    ['ate' => 3751.05, 'aliquota' => 15.0, 'deducao' => 394.16],
                    ['ate' => 4664.68, 'aliquota' => 22.5, 'deducao' => 675.49],
                    ['ate' => 99999999.99, 'aliquota' => 27.5, 'deducao' => 908.73],
                ]),
                'irrf_dependente' => 189.59,
                'irrf_desconto_simplificado' => 607.20,
                'fgts_percentual' => 8.00,
                'fgts_multa_percentual' => 40.00,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('rh_desligamentos') && Schema::hasColumn('rh_desligamentos', 'rescisao_id')) {
            Schema::table('rh_desligamentos', function (Blueprint $table) {
                $table->dropColumn('rescisao_id');
            });
        }

        Schema::dropIfExists('rh_rescisao_itens');
        Schema::dropIfExists('rh_rescisoes');
        Schema::dropIfExists('rh_parametros_fiscais');
    }
};
