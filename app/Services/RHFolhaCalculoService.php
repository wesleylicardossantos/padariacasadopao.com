<?php

namespace App\Services;

use App\Support\RHCompetenciaHelper;

class RHFolhaCalculoService
{
    public function __construct(private RHFolhaEngineService $engine)
    {
    }

    public static function competencia($mes, $ano): array
    {
        $mes = RHCompetenciaHelper::numero($mes);
        $ano = (int) $ano > 0 ? (int) $ano : (int) date('Y');

        return [
            'mes' => $mes,
            'ano' => $ano,
            'mes_nome' => RHCompetenciaHelper::nome($mes),
            'mes_padded' => str_pad((string) $mes, 2, '0', STR_PAD_LEFT),
        ];
    }

    public function calcularFuncionario($funcionario, $mes, $ano)
    {
        return $this->engine->calcularFuncionario($funcionario, (int) $mes, (int) $ano, (int) ($funcionario->empresa_id ?? 0));
    }
}
