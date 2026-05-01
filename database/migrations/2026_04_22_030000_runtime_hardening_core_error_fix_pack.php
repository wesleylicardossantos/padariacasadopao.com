<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('funcionarios_ficha_admissao')) {
            Schema::create('funcionarios_ficha_admissao', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('funcionario_id')->index();
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->string('matricula')->nullable();
                $table->string('matricula_social')->nullable();
                $table->string('nome_pai')->nullable();
                $table->string('nome_mae')->nullable();
                $table->string('naturalidade')->nullable();
                $table->string('nacionalidade')->nullable();
                $table->string('uf_naturalidade', 2)->nullable();
                $table->date('data_nascimento')->nullable();
                $table->boolean('deficiencia_fisica')->default(false);
                $table->string('raca_cor')->nullable();
                $table->string('sexo')->nullable();
                $table->string('estado_civil')->nullable();
                $table->string('grau_instrucao')->nullable();
                $table->string('ctps_numero')->nullable();
                $table->string('ctps_serie')->nullable();
                $table->string('ctps_uf', 2)->nullable();
                $table->date('ctps_data_expedicao')->nullable();
                $table->string('pis_numero')->nullable();
                $table->date('pis_data_cadastro')->nullable();
                $table->string('rg_orgao_emissor')->nullable();
                $table->date('rg_data_emissao')->nullable();
                $table->string('titulo_eleitor')->nullable();
                $table->string('titulo_zona')->nullable();
                $table->string('titulo_secao')->nullable();
                $table->string('certificado_reservista')->nullable();
                $table->string('cnh_numero')->nullable();
                $table->string('cnh_categoria')->nullable();
                $table->date('cnh_validade')->nullable();
                $table->date('cnh_primeira_habilitacao')->nullable();
                $table->date('data_admissao')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pdv_offline_syncs')) {
            Schema::create('pdv_offline_syncs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->index();
                $table->unsignedBigInteger('usuario_id')->nullable()->index();
                $table->string('uuid_local', 120);
                $table->unsignedBigInteger('venda_caixa_id')->nullable()->index();
                $table->string('status', 40)->default('pendente')->index();
                $table->string('payload_hash', 64)->nullable();
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->text('erro')->nullable();
                $table->unsignedInteger('tentativas')->default(0);
                $table->timestamp('ultima_tentativa_em')->nullable();
                $table->timestamp('sincronizado_em')->nullable();
                $table->timestamps();
                $table->unique(['empresa_id', 'uuid_local'], 'pdv_offline_syncs_empresa_uuid_unique');
            });
        }

        if (Schema::hasTable('payments')) {
            if (!Schema::hasColumn('payments', 'status') && Schema::hasColumn('payments', 'estado')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('status', 60)->nullable()->after('empresa_id');
                });
                DB::statement('UPDATE payments SET status = estado WHERE status IS NULL AND estado IS NOT NULL');
            }
            if (!Schema::hasColumn('payments', 'estado') && Schema::hasColumn('payments', 'status')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('estado', 60)->nullable()->after('status');
                });
                DB::statement('UPDATE payments SET estado = status WHERE estado IS NULL AND status IS NOT NULL');
            }
        }
    }

    public function down(): void
    {
        // hardening sem rollback destrutivo
    }
};
