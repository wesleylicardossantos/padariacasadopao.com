<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHEmpresaEnterpriseController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = request()->empresa_id;
        $mes = (int)($request->mes ?: date('m'));
        $ano = (int)($request->ano ?: date('Y'));

        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));

        $receita = $this->sumReceita($empresaId, $inicio, $fim);
        $despesas = $this->sumDespesas($empresaId, $inicio, $fim);
        $rh = $this->sumRh($empresaId);
        $resultado = $receita - $despesas - $rh;
        $margem = $receita > 0 ? ($resultado / $receita) * 100 : 0;
        $pesoFolha = $receita > 0 ? ($rh / $receita) * 100 : 0;

        $funcionariosAtivos = Funcionario::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })->count();

        $receitaPorFuncionario = $funcionariosAtivos > 0 ? ($receita / $funcionariosAtivos) : 0;
        $custoPorFuncionario = $funcionariosAtivos > 0 ? ($rh / $funcionariosAtivos) : 0;

        $score = $this->scoreEmpresa($margem, $pesoFolha, $receitaPorFuncionario, $custoPorFuncionario, $resultado);
        $alertas = $this->buildAlertas($margem, $pesoFolha, $resultado, $receitaPorFuncionario, $custoPorFuncionario);

        $caixaProjetado = $receita - $despesas - $rh;
        $riscoCaixa = $caixaProjetado < 0 ? 'alto' : ($caixaProjetado < ($rh * 0.3) ? 'medio' : 'baixo');

        $setores = $this->custosPorSetor($empresaId);

        return view('rh.enterprise_total.index', compact(
            'mes',
            'ano',
            'receita',
            'despesas',
            'rh',
            'resultado',
            'margem',
            'pesoFolha',
            'funcionariosAtivos',
            'receitaPorFuncionario',
            'custoPorFuncionario',
            'score',
            'alertas',
            'caixaProjetado',
            'riscoCaixa',
            'setores'
        ));
    }

    public function alertas(Request $request)
    {
        $empresaId = request()->empresa_id;
        $mes = (int)($request->mes ?: date('m'));
        $ano = (int)($request->ano ?: date('Y'));
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));

        $receita = $this->sumReceita($empresaId, $inicio, $fim);
        $despesas = $this->sumDespesas($empresaId, $inicio, $fim);
        $rh = $this->sumRh($empresaId);
        $resultado = $receita - $despesas - $rh;
        $margem = $receita > 0 ? ($resultado / $receita) * 100 : 0;
        $pesoFolha = $receita > 0 ? ($rh / $receita) * 100 : 0;

        $funcionariosAtivos = Funcionario::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })->count();

        $receitaPorFuncionario = $funcionariosAtivos > 0 ? ($receita / $funcionariosAtivos) : 0;
        $custoPorFuncionario = $funcionariosAtivos > 0 ? ($rh / $funcionariosAtivos) : 0;

        $alertas = $this->buildAlertas($margem, $pesoFolha, $resultado, $receitaPorFuncionario, $custoPorFuncionario);

        return view('rh.enterprise_total.alertas', compact('mes', 'ano', 'alertas'));
    }

    private function sumReceita($empresaId, $inicio, $fim)
    {
        if (!Schema::hasTable('conta_recebers')) return 0;
        return (float) ContaReceber::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->sum('valor_integral');
    }

    private function sumDespesas($empresaId, $inicio, $fim)
    {
        if (!Schema::hasTable('conta_pagars')) return 0;
        return (float) ContaPagar::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->sum('valor_integral');
    }

    private function sumRh($empresaId)
    {
        $funcionarios = Funcionario::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })->get();

        $total = 0;
        foreach ($funcionarios as $funcionario) {
            $base = (float) ($funcionario->salario ?? 0);
            $extras = 0;

            if (Schema::hasTable('funcionario_eventos')) {
                $extras = (float) DB::table('funcionario_eventos')
                    ->where('funcionario_id', $funcionario->id)
                    ->where('ativo', 1)
                    ->where('condicao', 'soma')
                    ->sum('valor');
            }

            $total += $base + $extras + ($base * 0.28) + ($base * 0.08) + ($base / 12) + ($base / 12);
        }

        return $total;
    }

    private function scoreEmpresa($margem, $pesoFolha, $receitaPorFuncionario, $custoPorFuncionario, $resultado)
    {
        $score = 100;

        if ($resultado < 0) $score -= 30;
        if ($margem < 5) $score -= 20;
        elseif ($margem < 10) $score -= 10;

        if ($pesoFolha > 45) $score -= 25;
        elseif ($pesoFolha > 35) $score -= 10;

        if ($custoPorFuncionario > 0 && $receitaPorFuncionario < ($custoPorFuncionario * 1.8)) $score -= 15;

        if ($score < 0) $score = 0;
        if ($score > 100) $score = 100;

        return (int) round($score);
    }

    private function buildAlertas($margem, $pesoFolha, $resultado, $receitaPorFuncionario, $custoPorFuncionario)
    {
        $alertas = [];

        if ($resultado < 0) $alertas[] = ['nivel' => 'critico', 'texto' => 'Resultado operacional negativo. Priorize corte de custos e revisão da folha.'];
        if ($pesoFolha > 45) $alertas[] = ['nivel' => 'critico', 'texto' => 'Folha acima de 45% do faturamento. Risco elevado para caixa.'];
        elseif ($pesoFolha > 35) $alertas[] = ['nivel' => 'alerta', 'texto' => 'Folha acima de 35% do faturamento. Monitore novas contratações.'];
        if ($margem < 5) $alertas[] = ['nivel' => 'alerta', 'texto' => 'Margem operacional abaixo de 5%.'];
        if ($custoPorFuncionario > 0 && $receitaPorFuncionario < ($custoPorFuncionario * 1.8)) $alertas[] = ['nivel' => 'alerta', 'texto' => 'Receita por colaborador está baixa em relação ao custo médio.'];
        if (empty($alertas)) $alertas[] = ['nivel' => 'ok', 'texto' => 'Indicadores saudáveis no período atual.'];

        return $alertas;
    }

    private function custosPorSetor($empresaId)
    {
        $funcionarios = Funcionario::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })->get();

        $setores = [];
        foreach ($funcionarios as $funcionario) {
            $setor = trim((string) ($funcionario->funcao ?? 'Sem função'));
            $base = (float) ($funcionario->salario ?? 0);
            $extras = 0;

            if (Schema::hasTable('funcionario_eventos')) {
                $extras = (float) DB::table('funcionario_eventos')
                    ->where('funcionario_id', $funcionario->id)
                    ->where('ativo', 1)
                    ->where('condicao', 'soma')
                    ->sum('valor');
            }

            $custo = $base + $extras + ($base * 0.28) + ($base * 0.08) + ($base / 12) + ($base / 12);

            if (!isset($setores[$setor])) $setores[$setor] = 0;
            $setores[$setor] += $custo;
        }

        arsort($setores);

        $saida = [];
        foreach ($setores as $setor => $custo) {
            $saida[] = ['setor' => $setor, 'custo' => $custo];
        }
        return array_slice($saida, 0, 10);
    }
}
