<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use App\Models\RHFolhaFechamento;

class RHFolhaLockService
{
    public static function competenciaFechada($empresaId, $mes = null, $ano = null)
    {
        // 🔥 CORREÇÃO:
        // só valida se mes/ano forem informados
        if (!$mes || !$ano) {
            return false;
        }

        if (!Schema::hasTable('rh_folha_fechamentos')) {
            return false;
        }

        return RHFolhaFechamento::where('empresa_id', $empresaId)
            ->where('mes', (int)$mes)
            ->where('ano', (int)$ano)
            ->where('status', 'fechado')
            ->exists();
    }

    public static function bloquearSeFechada($empresaId, $mes = null, $ano = null)
    {
        return self::competenciaFechada($empresaId, $mes, $ano);
    }
}
