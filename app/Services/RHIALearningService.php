<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RHIALearningService
{
    public static function tabelaDisponivel()
    {
        return Schema::hasTable('rh_ia_decisoes');
    }

    public static function registrar($empresaId, $acao, $titulo, $decisao, array $contexto = [])
    {
        if (!self::tabelaDisponivel()) {
            return false;
        }

        DB::table('rh_ia_decisoes')->insert([
            'empresa_id' => $empresaId,
            'acao' => $acao,
            'titulo' => $titulo,
            'decisao' => $decisao,
            'contexto_json' => json_encode($contexto, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    public static function scoreAcao($empresaId, $acao)
    {
        if (!self::tabelaDisponivel()) {
            return 0;
        }

        $aprovados = (int) DB::table('rh_ia_decisoes')
            ->where('empresa_id', $empresaId)
            ->where('acao', $acao)
            ->where('decisao', 'aprovado')
            ->count();

        $rejeitados = (int) DB::table('rh_ia_decisoes')
            ->where('empresa_id', $empresaId)
            ->where('acao', $acao)
            ->where('decisao', 'rejeitado')
            ->count();

        $total = $aprovados + $rejeitados;
        if ($total === 0) {
            return 0;
        }

        return round((($aprovados - $rejeitados) / $total) * 100, 2);
    }

    public static function resumoEmpresa($empresaId)
    {
        if (!self::tabelaDisponivel()) {
            return [
                'aprovados' => 0,
                'rejeitados' => 0,
                'total' => 0,
                'taxa_aprovacao' => 0,
            ];
        }

        $aprovados = (int) DB::table('rh_ia_decisoes')
            ->where('empresa_id', $empresaId)
            ->where('decisao', 'aprovado')
            ->count();

        $rejeitados = (int) DB::table('rh_ia_decisoes')
            ->where('empresa_id', $empresaId)
            ->where('decisao', 'rejeitado')
            ->count();

        $total = $aprovados + $rejeitados;
        $taxa = $total > 0 ? round(($aprovados / $total) * 100, 2) : 0;

        return [
            'aprovados' => $aprovados,
            'rejeitados' => $rejeitados,
            'total' => $total,
            'taxa_aprovacao' => $taxa,
        ];
    }

    public static function topAcoes($empresaId, $limit = 10)
    {
        if (!self::tabelaDisponivel()) {
            return collect();
        }

        return DB::table('rh_ia_decisoes')
            ->selectRaw('acao, max(titulo) as titulo,
                sum(case when decisao = "aprovado" then 1 else 0 end) as aprovados,
                sum(case when decisao = "rejeitado" then 1 else 0 end) as rejeitados,
                count(*) as total')
            ->where('empresa_id', $empresaId)
            ->groupBy('acao')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $item->score = $item->total > 0
                    ? round((($item->aprovados - $item->rejeitados) / $item->total) * 100, 2)
                    : 0;
                return $item;
            });
    }

    public static function aplicarPrioridade($empresaId, array $sugestoes)
    {
        foreach ($sugestoes as &$sugestao) {
            $sugestao['score_aprendizado'] = self::scoreAcao($empresaId, $sugestao['acao'] ?? '');
        }

        usort($sugestoes, function ($a, $b) {
            return ($b['score_aprendizado'] ?? 0) <=> ($a['score_aprendizado'] ?? 0);
        });

        return $sugestoes;
    }
}
