<?php

namespace App\Services;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHAnalyticsService
{
    public static function resumoCompetencia($empresaId, $mes, $ano)
    {
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));

        $receita = self::sumReceita($empresaId, $inicio, $fim);
        $despesas = self::sumDespesas($empresaId, $inicio, $fim);
        $rh = self::sumRh($empresaId);
        $resultado = $receita - $despesas - $rh;
        $pesoFolha = $receita > 0 ? ($rh / $receita) * 100 : 0;
        $margem = $receita > 0 ? ($resultado / $receita) * 100 : 0;

        $funcionariosAtivos = Funcionario::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })->count();

        $receitaPorFuncionario = $funcionariosAtivos > 0 ? ($receita / $funcionariosAtivos) : 0;
        $custoPorFuncionario = $funcionariosAtivos > 0 ? ($rh / $funcionariosAtivos) : 0;

        return [
            'mes' => (int)$mes,
            'ano' => (int)$ano,
            'receita' => (float)$receita,
            'despesas' => (float)$despesas,
            'rh' => (float)$rh,
            'resultado' => (float)$resultado,
            'peso_folha' => (float)$pesoFolha,
            'margem' => (float)$margem,
            'funcionarios_ativos' => (int)$funcionariosAtivos,
            'receita_por_funcionario' => (float)$receitaPorFuncionario,
            'custo_por_funcionario' => (float)$custoPorFuncionario,
        ];
    }

    public static function historico($empresaId, $mes, $ano, $meses = 6)
    {
        $saida = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $ref = strtotime(sprintf('%04d-%02d-01', $ano, $mes) . " -{$i} month");
            $m = (int)date('m', $ref);
            $a = (int)date('Y', $ref);
            $saida[] = self::resumoCompetencia($empresaId, $m, $a);
        }
        return $saida;
    }

    public static function score(array $resumo)
    {
        $score = 100;

        if ($resumo['resultado'] < 0) $score -= 30;
        if ($resumo['margem'] < 5) $score -= 20;
        elseif ($resumo['margem'] < 10) $score -= 10;

        if ($resumo['peso_folha'] > 45) $score -= 25;
        elseif ($resumo['peso_folha'] > 35) $score -= 10;

        if ($resumo['custo_por_funcionario'] > 0 && $resumo['receita_por_funcionario'] < ($resumo['custo_por_funcionario'] * 1.8)) {
            $score -= 15;
        }

        if ($score < 0) $score = 0;
        if ($score > 100) $score = 100;

        return (int)round($score);
    }

    public static function alertas(array $resumo)
    {
        $alertas = [];

        if ($resumo['resultado'] < 0) {
            $alertas[] = ['nivel' => 'critico', 'texto' => 'Resultado operacional negativo.'];
        }
        if ($resumo['peso_folha'] > 45) {
            $alertas[] = ['nivel' => 'critico', 'texto' => 'Folha acima de 45% do faturamento.'];
        } elseif ($resumo['peso_folha'] > 35) {
            $alertas[] = ['nivel' => 'alerta', 'texto' => 'Folha acima de 35% do faturamento.'];
        }
        if ($resumo['margem'] < 5) {
            $alertas[] = ['nivel' => 'alerta', 'texto' => 'Margem operacional abaixo de 5%.'];
        }
        if ($resumo['custo_por_funcionario'] > 0 && $resumo['receita_por_funcionario'] < ($resumo['custo_por_funcionario'] * 1.8)) {
            $alertas[] = ['nivel' => 'alerta', 'texto' => 'Receita por colaborador está baixa em relação ao custo médio.'];
        }
        if (empty($alertas)) {
            $alertas[] = ['nivel' => 'ok', 'texto' => 'Indicadores saudáveis no período atual.'];
        }

        return $alertas;
    }

    public static function tendenciaHistorica(array $historico)
    {
        $count = count($historico);
        if ($count < 2) {
            return ['resultado' => 'estavel', 'receita' => 'estavel', 'rh' => 'estavel'];
        }

        $primeiro = $historico[0];
        $ultimo = $historico[$count - 1];

        return [
            'resultado' => self::comparar($primeiro['resultado'], $ultimo['resultado']),
            'receita' => self::comparar($primeiro['receita'], $ultimo['receita']),
            'rh' => self::comparar($primeiro['rh'], $ultimo['rh']),
        ];
    }

    public static function recomendacoesAutonomas(array $resumo, array $historico)
    {
        $acoes = [];
        $tendencia = self::tendenciaHistorica($historico);

        if ($resumo['resultado'] < 0) {
            $acoes[] = [
                'nivel' => 'critico',
                'titulo' => 'Ação imediata',
                'acao' => 'Suspender novas contratações e revisar despesas operacionais.',
                'motivo' => 'Resultado operacional negativo na competência atual.'
            ];
        }

        if ($resumo['peso_folha'] > 45) {
            $acoes[] = [
                'nivel' => 'critico',
                'titulo' => 'Reduzir pressão da folha',
                'acao' => 'Rever horas extras, eventos variáveis e contratações.',
                'motivo' => 'Folha acima de 45% do faturamento.'
            ];
        } elseif ($resumo['peso_folha'] <= 30 && $resumo['margem'] >= 12) {
            $acoes[] = [
                'nivel' => 'positivo',
                'titulo' => 'Espaço para crescimento',
                'acao' => 'Há espaço para contratação planejada ou expansão comercial.',
                'motivo' => 'Folha saudável e margem forte.'
            ];
        }

        if ($tendencia['resultado'] === 'queda' && $tendencia['rh'] === 'crescimento') {
            $acoes[] = [
                'nivel' => 'alerta',
                'titulo' => 'Tendência perigosa',
                'acao' => 'Segurar expansão e reforçar receita antes de ampliar custo fixo.',
                'motivo' => 'Resultado em queda com custo de RH em crescimento.'
            ];
        }

        if ($resumo['margem'] < 5) {
            $acoes[] = [
                'nivel' => 'alerta',
                'titulo' => 'Margem apertada',
                'acao' => 'Evitar aumentos lineares e focar em produtividade.',
                'motivo' => 'Margem operacional abaixo de 5%.'
            ];
        }

        if (empty($acoes)) {
            $acoes[] = [
                'nivel' => 'positivo',
                'titulo' => 'Operação saudável',
                'acao' => 'Manter crescimento controlado e acompanhamento mensal.',
                'motivo' => 'Sem riscos relevantes no cenário atual.'
            ];
        }

        return $acoes;
    }

    public static function previsaoTresMeses(array $historico)
    {
        $receitas = array_column($historico, 'receita');
        $rhs = array_column($historico, 'rh');
        $despesas = array_column($historico, 'despesas');

        $receitaMedia = self::media($receitas);
        $rhMedia = self::media($rhs);
        $despesaMedia = self::media($despesas);

        $cresReceita = self::variacaoLista($receitas);
        $cresRh = self::variacaoLista($rhs);
        $cresDespesa = self::variacaoLista($despesas);

        $saida = [];
        for ($i = 1; $i <= 3; $i++) {
            $rec = $receitaMedia * (1 + (($cresReceita / 100) * $i));
            $rh = $rhMedia * (1 + (($cresRh / 100) * $i));
            $desp = $despesaMedia * (1 + (($cresDespesa / 100) * $i));
            $resultado = $rec - $rh - $desp;
            $margem = $rec > 0 ? ($resultado / $rec) * 100 : 0;

            $saida[] = [
                'passo' => $i,
                'receita' => $rec,
                'rh' => $rh,
                'despesas' => $desp,
                'resultado' => $resultado,
                'margem' => $margem,
            ];
        }

        return $saida;
    }

    public static function custosPorSetor($empresaId)
    {
        $funcionarios = Funcionario::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })->get();

        $setores = [];
        foreach ($funcionarios as $funcionario) {
            $setor = trim((string)($funcionario->funcao ?? 'Sem função'));
            $base = (float)($funcionario->salario ?? 0);
            $extras = 0;

            if (Schema::hasTable('funcionario_eventos')) {
                $extras = (float)DB::table('funcionario_eventos')
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

    private static function sumReceita($empresaId, $inicio, $fim)
    {
        if (!Schema::hasTable('conta_recebers')) return 0;
        return (float)ContaReceber::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->sum('valor_integral');
    }

    private static function sumDespesas($empresaId, $inicio, $fim)
    {
        if (!Schema::hasTable('conta_pagars')) return 0;
        return (float)ContaPagar::where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->sum('valor_integral');
    }

    private static function sumRh($empresaId)
    {
        $funcionarios = Funcionario::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            })->get();

        $total = 0;
        foreach ($funcionarios as $funcionario) {
            $base = (float)($funcionario->salario ?? 0);
            $extras = 0;

            if (Schema::hasTable('funcionario_eventos')) {
                $extras = (float)DB::table('funcionario_eventos')
                    ->where('funcionario_id', $funcionario->id)
                    ->where('ativo', 1)
                    ->where('condicao', 'soma')
                    ->sum('valor');
            }

            $total += $base + $extras + ($base * 0.28) + ($base * 0.08) + ($base / 12) + ($base / 12);
        }

        return $total;
    }

    private static function comparar($primeiro, $ultimo)
    {
        if ($ultimo > $primeiro) return 'crescimento';
        if ($ultimo < $primeiro) return 'queda';
        return 'estavel';
    }

    private static function media($valores)
    {
        if (count($valores) === 0) return 0;
        return array_sum($valores) / count($valores);
    }

    private static function variacaoLista($valores)
    {
        $count = count($valores);
        if ($count < 2) return 0;
        $primeiro = (float)$valores[0];
        $ultimo = (float)$valores[$count - 1];
        if ($primeiro <= 0) return 0;
        return (($ultimo - $primeiro) / $primeiro);
    }
}
