<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHIAAutonomaController extends Controller
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

        $funcionariosAtivos = Funcionario::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })->count();

        $pesoFolha = $receita > 0 ? ($rh / $receita) * 100 : 0;
        $margem = $receita > 0 ? ($resultado / $receita) * 100 : 0;
        $receitaPorFuncionario = $funcionariosAtivos > 0 ? ($receita / $funcionariosAtivos) : 0;
        $custoPorFuncionario = $funcionariosAtivos > 0 ? ($rh / $funcionariosAtivos) : 0;

        $acoes = [];

        if ($resultado < 0) {
            $acoes[] = [
                'nivel' => 'critico',
                'titulo' => 'Ação imediata recomendada',
                'acao' => 'Suspender novas contratações e revisar despesas operacionais não essenciais.',
                'motivo' => 'A empresa está com resultado operacional negativo no período.'
            ];
        }

        if ($pesoFolha > 45) {
            $acoes[] = [
                'nivel' => 'critico',
                'titulo' => 'Reduzir pressão da folha',
                'acao' => 'Reavaliar horas extras, eventos variáveis e crescimento da equipe antes de expandir.',
                'motivo' => 'A folha passou de 45% do faturamento.'
            ];
        } elseif ($pesoFolha <= 30 && $margem >= 12) {
            $acoes[] = [
                'nivel' => 'positivo',
                'titulo' => 'Espaço controlado para expansão',
                'acao' => 'É possível considerar contratação planejada ou reforço comercial.',
                'motivo' => 'A folha está saudável frente à receita e a margem está forte.'
            ];
        }

        if ($custoPorFuncionario > 0 && $receitaPorFuncionario < ($custoPorFuncionario * 1.8)) {
            $acoes[] = [
                'nivel' => 'alerta',
                'titulo' => 'Produtividade sob pressão',
                'acao' => 'Priorizar aumento de receita por colaborador antes de elevar custos fixos.',
                'motivo' => 'A receita por funcionário está baixa em relação ao custo médio.'
            ];
        }

        if ($margem < 5) {
            $acoes[] = [
                'nivel' => 'alerta',
                'titulo' => 'Margem apertada',
                'acao' => 'Evitar aumentos salariais lineares e focar em metas, eficiência e receita.',
                'motivo' => 'A margem operacional está abaixo de 5%.'
            ];
        }

        if (empty($acoes)) {
            $acoes[] = [
                'nivel' => 'positivo',
                'titulo' => 'Operação saudável',
                'acao' => 'Manter crescimento controlado e acompanhar mensalmente os indicadores.',
                'motivo' => 'Os indicadores atuais não mostram risco relevante.'
            ];
        }

        $simQtd = (int)($request->sim_qtd ?: 0);
        $simSalario = (float)($request->sim_salario ?: 0);
        $custoUnitario = $simSalario > 0 ? $simSalario + ($simSalario * 0.28) + ($simSalario * 0.08) + ($simSalario / 12) + ($simSalario / 12) : 0;
        $impacto = $simQtd * $custoUnitario;
        $resultadoSimulado = $resultado - $impacto;
        $margemSimulada = $receita > 0 ? ($resultadoSimulado / $receita) * 100 : 0;

        $parecer = null;
        if ($simQtd > 0 && $simSalario > 0) {
            if ($resultadoSimulado < 0) {
                $parecer = 'IA: não contratar agora. A simulação leva a empresa para resultado negativo.';
            } elseif ($margemSimulada < 5) {
                $parecer = 'IA: contratação possível, mas com risco. A margem ficará muito apertada.';
            } else {
                $parecer = 'IA: contratação viável no cenário atual, com manutenção de resultado positivo.';
            }
        }

        return view('rh.ia_autonoma.index', compact(
            'mes',
            'ano',
            'receita',
            'despesas',
            'rh',
            'resultado',
            'funcionariosAtivos',
            'pesoFolha',
            'margem',
            'receitaPorFuncionario',
            'custoPorFuncionario',
            'acoes',
            'simQtd',
            'simSalario',
            'impacto',
            'resultadoSimulado',
            'margemSimulada',
            'parecer'
        ));
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
}
