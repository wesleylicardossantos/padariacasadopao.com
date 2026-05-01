<?php

namespace App\Support;

class RHCompetenciaHelper
{
    private const MAP = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro',
    ];

    public static function nome(int $mes): string
    {
        return self::MAP[max(1, min(12, $mes))] ?? 'janeiro';
    }

    public static function numero(int|string|null $mes): int
    {
        if (is_int($mes) || (is_string($mes) && is_numeric(trim($mes)))) {
            $numero = (int) $mes;
            return max(1, min(12, $numero));
        }

        $mesNormalizado = self::normalizarTexto((string) $mes);
        $mapaReverso = array_flip(array_map([self::class, 'normalizarTexto'], self::MAP));

        return isset($mapaReverso[$mesNormalizado]) ? (int) $mapaReverso[$mesNormalizado] : (int) date('m');
    }

    public static function padded(int|string|null $mes): string
    {
        return str_pad((string) self::numero($mes), 2, '0', STR_PAD_LEFT);
    }

    public static function formatar(int|string|null $mes, int|string|null $ano): string
    {
        $ano = (int) ($ano ?: date('Y'));
        return self::padded($mes) . '/' . $ano;
    }

    public static function orderByMesCase(string $column = 'mes'): string
    {
        return "CASE {$column}
            WHEN 'janeiro' THEN 1
            WHEN 'fevereiro' THEN 2
            WHEN 'março' THEN 3
            WHEN 'marco' THEN 3
            WHEN 'abril' THEN 4
            WHEN 'maio' THEN 5
            WHEN 'junho' THEN 6
            WHEN 'julho' THEN 7
            WHEN 'agosto' THEN 8
            WHEN 'setembro' THEN 9
            WHEN 'outubro' THEN 10
            WHEN 'novembro' THEN 11
            WHEN 'dezembro' THEN 12
            ELSE {$column}
        END";
    }

    private static function normalizarTexto(string $texto): string
    {
        $texto = trim(mb_strtolower($texto));
        return str_replace('ç', 'c', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto);
    }
}
