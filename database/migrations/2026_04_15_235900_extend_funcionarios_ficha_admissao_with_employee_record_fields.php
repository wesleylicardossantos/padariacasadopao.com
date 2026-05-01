<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('funcionarios_ficha_admissao')) {
            return;
        }

        Schema::table('funcionarios_ficha_admissao', function (Blueprint $table) {
            $columns = [
                'matricula' => fn() => $table->string('matricula', 50)->nullable()->after('funcionario_id'),
                'matricula_social' => fn() => $table->string('matricula_social', 50)->nullable()->after('matricula'),
                'nacionalidade' => fn() => $table->string('nacionalidade', 80)->nullable()->after('naturalidade'),
                'certificado_reservista' => fn() => $table->string('certificado_reservista', 80)->nullable()->after('titulo_secao'),
                'tipo_habilitacao' => fn() => $table->string('tipo_habilitacao', 80)->nullable()->after('cnh_primeira_habilitacao'),
                'registro_profissional' => fn() => $table->string('registro_profissional', 80)->nullable()->after('tipo_habilitacao'),
                'orgao_registro_profissional' => fn() => $table->string('orgao_registro_profissional', 120)->nullable()->after('registro_profissional'),
                'data_opcao_fgts' => fn() => $table->date('data_opcao_fgts')->nullable()->after('data_admissao'),
                'forma_pagamento' => fn() => $table->string('forma_pagamento', 80)->nullable()->after('data_opcao_fgts'),
                'indicativo_admissao' => fn() => $table->string('indicativo_admissao', 120)->nullable()->after('forma_pagamento'),
                'numero_processo_trabalhista' => fn() => $table->string('numero_processo_trabalhista', 80)->nullable()->after('indicativo_admissao'),
                'categoria_trabalhador' => fn() => $table->string('categoria_trabalhador', 180)->nullable()->after('numero_processo_trabalhista'),
                'tipo_contrato_trabalho' => fn() => $table->string('tipo_contrato_trabalho', 180)->nullable()->after('categoria_trabalhador'),
                'natureza_atividade' => fn() => $table->string('natureza_atividade', 180)->nullable()->after('tipo_contrato_trabalho'),
                'departamento' => fn() => $table->string('departamento', 120)->nullable()->after('natureza_atividade'),
                'cbo' => fn() => $table->string('cbo', 30)->nullable()->after('departamento'),
                'descanso_semanal' => fn() => $table->string('descanso_semanal', 60)->nullable()->after('cbo'),
                'horas_mes' => fn() => $table->decimal('horas_mes', 8, 2)->nullable()->after('descanso_semanal'),
                'horas_semana' => fn() => $table->decimal('horas_semana', 8, 2)->nullable()->after('horas_mes'),
                'salario_variavel_descricao' => fn() => $table->string('salario_variavel_descricao', 180)->nullable()->after('horas_semana'),
                'dependentes_salario_familia' => fn() => $table->unsignedInteger('dependentes_salario_familia')->nullable()->after('salario_variavel_descricao'),
                'dependentes_irrf' => fn() => $table->unsignedInteger('dependentes_irrf')->nullable()->after('dependentes_salario_familia'),
            ];

            foreach ($columns as $name => $definition) {
                if (!Schema::hasColumn('funcionarios_ficha_admissao', $name)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('funcionarios_ficha_admissao')) {
            return;
        }

        Schema::table('funcionarios_ficha_admissao', function (Blueprint $table) {
            $drops = [
                'matricula',
                'matricula_social',
                'nacionalidade',
                'certificado_reservista',
                'tipo_habilitacao',
                'registro_profissional',
                'orgao_registro_profissional',
                'data_opcao_fgts',
                'forma_pagamento',
                'indicativo_admissao',
                'numero_processo_trabalhista',
                'categoria_trabalhador',
                'tipo_contrato_trabalho',
                'natureza_atividade',
                'departamento',
                'cbo',
                'descanso_semanal',
                'horas_mes',
                'horas_semana',
                'salario_variavel_descricao',
                'dependentes_salario_familia',
                'dependentes_irrf',
            ];

            foreach ($drops as $column) {
                if (Schema::hasColumn('funcionarios_ficha_admissao', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
