<?php

namespace App\Modules\RH\Application\Funcionario;

use App\Models\Funcionario;
use App\Models\FuncionarioFichaAdmissao;
use App\Services\RHDefaultPayrollEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FuncionarioService
{
    public function __construct(private RHDefaultPayrollEventService $defaultPayrollEvents)
    {
    }

    public function store(Request $request, int $empresaId): Funcionario
    {
        return DB::transaction(function () use ($request, $empresaId) {
            $normalizado = $this->normalizaCampos($request);
            $funcionario = Funcionario::create($this->dadosFuncionario($request, $empresaId, $normalizado));
            $this->salvarFichaAdmissao($funcionario->id, $request, $empresaId, $normalizado);
            $this->defaultPayrollEvents->syncFuncionarioBaseEvents($funcionario->fresh(), $empresaId);
            return $funcionario;
        });
    }

    public function update(Funcionario $funcionario, Request $request, int $empresaId): Funcionario
    {
        return DB::transaction(function () use ($funcionario, $request, $empresaId) {
            $normalizado = $this->normalizaCampos($request);
            $funcionario->fill($this->dadosFuncionario($request, $empresaId, $normalizado));
            $funcionario->save();
            $this->salvarFichaAdmissao($funcionario->id, $request, $empresaId, $normalizado);
            $this->defaultPayrollEvents->syncFuncionarioBaseEvents($funcionario->fresh(), $empresaId);
            return $funcionario;
        });
    }

    private function normalizaCampos(Request $request): array
    {
        return [
            'salario' => __convert_value_bd($request->salario),
            'email' => $request->email ?? '',
            'percentual_comissao' => $request->percentual_comissao ? __convert_value_bd($request->percentual_comissao) : 0,
            'vt_preco_passagem' => ($request->vt_preco_passagem !== null && $request->vt_preco_passagem !== '')
                ? __convert_value_bd($request->vt_preco_passagem)
                : null,
            'usuario_id' => $request->usuario_id ?? null,
            'deficiencia_fisica' => $request->boolean('deficiencia_fisica'),
            'possui_dependentes' => $request->boolean('possui_dependentes'),
            'vale_transporte' => $request->boolean('vale_transporte'),
            'nao_trabalha_sabado' => $request->boolean('nao_trabalha_sabado'),
            'contrato_experiencia' => $request->boolean('contrato_experiencia'),
        ];
    }

    private function dadosFuncionario(Request $request, int $empresaId, array $normalizado): array
    {
        return [
            'nome' => $request->nome,
            'bairro' => $request->bairro,
            'numero' => $request->numero,
            'rua' => $request->rua,
            'cpf' => $request->cpf,
            'rg' => $request->rg,
            'telefone' => $request->telefone,
            'celular' => $request->celular,
            'email' => $normalizado['email'],
            'data_registro' => $request->data_registro,
            'empresa_id' => $empresaId,
            'usuario_id' => $normalizado['usuario_id'],
            'percentual_comissao' => $normalizado['percentual_comissao'],
            'salario' => $normalizado['salario'],
            'funcao' => $request->funcao,
            'cidade_id' => $request->cidade_id,
            'ativo' => $request->has('ativo') ? (int) $request->boolean('ativo') : 1,
        ];
    }

    private function salvarFichaAdmissao(int $funcionarioId, Request $request, int $empresaId, array $normalizado): void
    {
        if (!Schema::hasTable('funcionarios_ficha_admissao')) {
            return;
        }

        $dadosFicha = [
            'empresa_id' => $empresaId,
            'funcionario_id' => $funcionarioId,
            'matricula' => $request->matricula,
            'matricula_social' => $request->matricula_social,
            'nome_pai' => $request->nome_pai,
            'nome_mae' => $request->nome_mae,
            'naturalidade' => $request->naturalidade,
            'nacionalidade' => $request->nacionalidade,
            'uf_naturalidade' => $request->uf_naturalidade,
            'data_nascimento' => $request->data_nascimento,
            'deficiencia_fisica' => $normalizado['deficiencia_fisica'],
            'raca_cor' => $request->raca_cor,
            'sexo' => $request->sexo,
            'estado_civil' => $request->estado_civil,
            'grau_instrucao' => $request->grau_instrucao,
            'ctps_numero' => $request->ctps_numero,
            'ctps_serie' => $request->ctps_serie,
            'ctps_uf' => $request->ctps_uf,
            'ctps_data_expedicao' => $request->ctps_data_expedicao,
            'pis_numero' => $request->pis_numero,
            'pis_data_cadastro' => $request->pis_data_cadastro,
            'rg_orgao_emissor' => $request->rg_orgao_emissor,
            'rg_data_emissao' => $request->rg_data_emissao,
            'titulo_eleitor' => $request->titulo_eleitor,
            'titulo_zona' => $request->titulo_zona,
            'titulo_secao' => $request->titulo_secao,
            'certificado_reservista' => $request->certificado_reservista,
            'cnh_numero' => $request->cnh_numero,
            'cnh_categoria' => $request->cnh_categoria,
            'cnh_validade' => $request->cnh_validade,
            'cnh_primeira_habilitacao' => $request->cnh_primeira_habilitacao,
            'tipo_habilitacao' => $request->tipo_habilitacao,
            'registro_profissional' => $request->registro_profissional,
            'orgao_registro_profissional' => $request->orgao_registro_profissional,
            'possui_dependentes' => $normalizado['possui_dependentes'],
            'dependentes_texto' => $request->dependentes_texto,
            'vale_transporte' => $normalizado['vale_transporte'],
            'vt_linhas' => $request->vt_linhas,
            'vt_preco_passagem' => $normalizado['vt_preco_passagem'],
            'nao_trabalha_sabado' => $normalizado['nao_trabalha_sabado'],
            'data_admissao' => $request->input('data_admissao') ?: $request->input('data_registro'),
            'data_opcao_fgts' => $request->data_opcao_fgts,
            'data_exame_admissional' => $request->data_exame_admissional,
            'contrato_experiencia' => $normalizado['contrato_experiencia'],
            'experiencia_tipo' => $request->experiencia_tipo,
            'forma_pagamento' => $request->forma_pagamento,
            'indicativo_admissao' => $request->indicativo_admissao,
            'numero_processo_trabalhista' => $request->numero_processo_trabalhista,
            'categoria_trabalhador' => $request->categoria_trabalhador,
            'tipo_contrato_trabalho' => $request->tipo_contrato_trabalho,
            'natureza_atividade' => $request->natureza_atividade,
            'departamento' => $request->departamento,
            'cbo' => $request->cbo,
            'descanso_semanal' => $request->descanso_semanal,
            'horas_mes' => $request->horas_mes,
            'horas_semana' => $request->horas_semana,
            'salario_variavel_descricao' => $request->salario_variavel_descricao,
            'dependentes_salario_familia' => $request->dependentes_salario_familia,
            'dependentes_irrf' => $request->dependentes_irrf,
            'conta_salario' => $request->conta_salario,
            'agencia' => $request->agencia,
            'banco' => $request->banco,
            'ficha_preenchida_por' => $request->ficha_preenchida_por,
            'observacoes' => $request->observacoes,
        ];

        $model = FuncionarioFichaAdmissao::firstOrNew(['funcionario_id' => $funcionarioId]);
        $model->fill($dadosFicha);
        $model->save();
    }
}
