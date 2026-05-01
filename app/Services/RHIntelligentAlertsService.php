<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHIntelligentAlertsService
{
    public static function gerar($empresaId, $mes, $ano)
    {
        $resumo = RHAnalyticsService::resumoCompetencia($empresaId, $mes, $ano);
        $preditivo = RHPredictiveService::gerar($empresaId, $mes, $ano, 6);

        $alertas = [];

        if (($resumo['peso_folha'] ?? 0) > 45) {
            $alertas[] = self::montar(
                'folha_alta',
                'critico',
                'Folha acima do limite',
                'A folha ultrapassou 45% do faturamento.'
            );
        } elseif (($resumo['peso_folha'] ?? 0) > 35) {
            $alertas[] = self::montar(
                'folha_pressao',
                'alerta',
                'Folha em zona de atenção',
                'A folha ultrapassou 35% do faturamento.'
            );
        }

        if (($resumo['resultado'] ?? 0) < 0) {
            $alertas[] = self::montar(
                'resultado_negativo',
                'critico',
                'Resultado negativo',
                'A empresa está operando com resultado negativo no período.'
            );
        }

        if (($resumo['margem'] ?? 0) < 5) {
            $alertas[] = self::montar(
                'margem_baixa',
                'alerta',
                'Margem baixa',
                'A margem operacional caiu abaixo de 5%.'
            );
        }

        foreach (($preditivo['projecoes'] ?? []) as $proj) {
            if (($proj['resultado'] ?? 0) < 0) {
                $alertas[] = self::montar(
                    'prejuizo_futuro',
                    'critico',
                    'Prejuízo projetado',
                    'A projeção indica possível prejuízo em M+' . ($proj['passo'] ?? 0) . '.'
                );
                break;
            }
        }

        return $alertas;
    }

    public static function persistir($empresaId, array $alertas)
    {
        if (!Schema::hasTable('rh_alertas_inteligentes')) {
            return 0;
        }

        $count = 0;
        foreach ($alertas as $alerta) {
            DB::table('rh_alertas_inteligentes')->insert([
                'empresa_id' => $empresaId,
                'tipo' => $alerta['tipo'],
                'nivel' => $alerta['nivel'],
                'titulo' => $alerta['titulo'],
                'mensagem' => $alerta['mensagem'],
                'referencia_json' => json_encode($alerta, JSON_UNESCAPED_UNICODE),
                'lido' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    public static function listar($empresaId, $somenteNaoLidos = false, $limit = 50)
    {
        if (!Schema::hasTable('rh_alertas_inteligentes')) {
            return collect();
        }

        $q = DB::table('rh_alertas_inteligentes')
            ->where('empresa_id', $empresaId)
            ->orderByDesc('id')
            ->limit($limit);

        if ($somenteNaoLidos) {
            $q->where('lido', 0);
        }

        return $q->get();
    }

    public static function marcarComoLido($empresaId, $id)
    {
        if (!Schema::hasTable('rh_alertas_inteligentes')) {
            return false;
        }

        return DB::table('rh_alertas_inteligentes')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update([
                'lido' => 1,
                'updated_at' => now(),
            ]);
    }

    private static function montar($tipo, $nivel, $titulo, $mensagem)
    {
        return compact('tipo', 'nivel', 'titulo', 'mensagem');
    }
}
