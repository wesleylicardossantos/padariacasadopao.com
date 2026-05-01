<?php

namespace App\Services;

class RHMachineLearningService
{
    public static function analisarHistorico(array $historico)
    {
        $receitas = array_column($historico, 'receita');
        $rhs = array_column($historico, 'rh');
        $resultados = array_column($historico, 'resultado');

        return [
            'tendencia_receita' => self::compararLista($receitas),
            'tendencia_rh' => self::compararLista($rhs),
            'tendencia_resultado' => self::compararLista($resultados),
            'media_receita' => self::media($receitas),
            'media_rh' => self::media($rhs),
            'media_resultado' => self::media($resultados),
            'volatilidade_resultado' => self::volatilidade($resultados),
        ];
    }

    public static function preverProximosMeses(array $historico, $meses = 3)
    {
        $insights = self::analisarHistorico($historico);

        $receitaBase = $insights['media_receita'];
        $rhBase = $insights['media_rh'];
        $resultadoBase = $insights['media_resultado'];

        $cresReceita = self::fatorTendencia($insights['tendencia_receita']);
        $cresRh = self::fatorTendencia($insights['tendencia_rh']);

        $saida = [];
        for ($i = 1; $i <= $meses; $i++) {
            $receita = $receitaBase * (1 + ($cresReceita * $i));
            $rh = $rhBase * (1 + ($cresRh * $i));
            $resultado = $receita - ($rh + max(0, ($receita - $resultadoBase - $rh)));
            $margem = $receita > 0 ? ($resultado / $receita) * 100 : 0;

            $saida[] = [
                'passo' => $i,
                'receita' => $receita,
                'rh' => $rh,
                'resultado' => $resultado,
                'margem' => $margem,
            ];
        }

        return $saida;
    }

    private static function compararLista(array $valores)
    {
        if (count($valores) < 2) return 'estavel';
        $primeiro = (float)$valores[0];
        $ultimo = (float)$valores[count($valores) - 1];
        if ($ultimo > $primeiro) return 'crescimento';
        if ($ultimo < $primeiro) return 'queda';
        return 'estavel';
    }

    private static function media(array $valores)
    {
        if (count($valores) === 0) return 0;
        return array_sum($valores) / count($valores);
    }

    private static function volatilidade(array $valores)
    {
        $media = self::media($valores);
        if ($media == 0 || count($valores) < 2) return 0;

        $soma = 0;
        foreach ($valores as $v) {
            $soma += pow($v - $media, 2);
        }
        return sqrt($soma / count($valores));
    }

    private static function fatorTendencia($tendencia)
    {
        if ($tendencia === 'crescimento') return 0.03;
        if ($tendencia === 'queda') return -0.03;
        return 0;
    }
}
