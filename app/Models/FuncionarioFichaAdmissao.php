<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuncionarioFichaAdmissao extends Model
{
    protected $table = 'funcionarios_ficha_admissao';

    protected $fillable = [
        'empresa_id',
        'funcionario_id',
        'matricula',
        'matricula_social',

        // Dados pessoais
        'nome_pai',
        'nome_mae',
        'naturalidade',
        'nacionalidade',
        'uf_naturalidade',
        'data_nascimento',
        'deficiencia_fisica',
        'raca_cor',
        'sexo',
        'estado_civil',
        'grau_instrucao',

        // Documentos
        'ctps_numero',
        'ctps_serie',
        'ctps_uf',
        'ctps_data_expedicao',
        'pis_numero',
        'pis_data_cadastro',
        'rg_orgao_emissor',
        'rg_data_emissao',
        'titulo_eleitor',
        'titulo_zona',
        'titulo_secao',
        'certificado_reservista',
        'cnh_numero',
        'cnh_categoria',
        'cnh_validade',
        'cnh_primeira_habilitacao',
        'tipo_habilitacao',
        'registro_profissional',
        'orgao_registro_profissional',

        // Dependentes
        'possui_dependentes',
        'dependentes_texto',

        // Vale transporte e jornada
        'vale_transporte',
        'vt_linhas',
        'vt_preco_passagem',
        'vt_quantidade_dia',
        'horario_seg_sex_entrada',
        'horario_seg_sex_saida',
        'horario_seg_sex_intervalo_inicio',
        'horario_seg_sex_intervalo_fim',
        'horario_sabado_entrada',
        'horario_sabado_saida',
        'nao_trabalha_sabado',

        // Admissão
        'data_admissao',
        'data_opcao_fgts',
        'data_exame_admissional',
        'contrato_experiencia',
        'experiencia_tipo',
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

        // Banco
        'conta_salario',
        'agencia',
        'banco',

        // Observações / controle
        'ficha_preenchida_por',
        'observacoes',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}
