<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('funcionarios_ficha_admissao')) {
            return;
        }

        Schema::create('funcionarios_ficha_admissao', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('funcionario_id')->unique();

            // Dados pessoais
            $table->string('nome_pai', 150)->nullable();
            $table->string('nome_mae', 150)->nullable();
            $table->string('naturalidade', 100)->nullable();
            $table->string('uf_naturalidade', 5)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->boolean('deficiencia_fisica')->default(false);
            $table->string('raca_cor', 30)->nullable();
            $table->string('sexo', 20)->nullable();
            $table->string('estado_civil', 30)->nullable();
            $table->string('grau_instrucao', 100)->nullable();

            // Documentos
            $table->string('ctps_numero', 50)->nullable();
            $table->string('ctps_serie', 50)->nullable();
            $table->string('ctps_uf', 5)->nullable();
            $table->date('ctps_data_expedicao')->nullable();

            $table->string('pis_numero', 50)->nullable();
            $table->date('pis_data_cadastro')->nullable();

            $table->string('rg_orgao_emissor', 60)->nullable();
            $table->date('rg_data_emissao')->nullable();

            $table->string('titulo_eleitor', 50)->nullable();
            $table->string('titulo_zona', 10)->nullable();
            $table->string('titulo_secao', 10)->nullable();

            $table->string('cnh_numero', 50)->nullable();
            $table->string('cnh_categoria', 10)->nullable();
            $table->date('cnh_validade')->nullable();
            $table->date('cnh_primeira_habilitacao')->nullable();

            // Dependentes
            $table->boolean('possui_dependentes')->default(false);
            $table->text('dependentes_texto')->nullable();

            // Vale transporte e jornada
            $table->boolean('vale_transporte')->default(false);
            $table->string('vt_linhas', 150)->nullable();
            $table->decimal('vt_preco_passagem', 10, 2)->nullable();
            $table->integer('vt_quantidade_dia')->nullable();

            $table->time('horario_seg_sex_entrada')->nullable();
            $table->time('horario_seg_sex_saida')->nullable();
            $table->time('horario_seg_sex_intervalo_inicio')->nullable();
            $table->time('horario_seg_sex_intervalo_fim')->nullable();

            $table->time('horario_sabado_entrada')->nullable();
            $table->time('horario_sabado_saida')->nullable();
            $table->boolean('nao_trabalha_sabado')->default(false);

            // Admissão
            $table->date('data_admissao')->nullable();
            $table->date('data_exame_admissional')->nullable();
            $table->boolean('contrato_experiencia')->default(false);
            $table->string('experiencia_tipo', 50)->nullable();

            // Banco
            $table->string('conta_salario', 50)->nullable();
            $table->string('agencia', 50)->nullable();
            $table->string('banco', 100)->nullable();

            // Observações / controle
            $table->string('ficha_preenchida_por', 150)->nullable();
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funcionarios_ficha_admissao');
    }
};
