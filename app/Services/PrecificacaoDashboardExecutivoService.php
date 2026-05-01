<?php

namespace App\Services;

use App\Models\PrecificacaoProduto;
use App\Support\PrecificacaoSchema;

class PrecificacaoDashboardExecutivoService
{
    public function __construct(private PrecificacaoAutoPricingService $autoPricing)
    {
    }

    public function montar(int $empresaId): array
    {
        if (! PrecificacaoSchema::hasTable('precificacao_produtos')) {
            return $this->vazio();
        }

        $produtos = PrecificacaoProduto::query()
            ->where('empresa_id', $empresaId)
            ->with(['receita.itens.insumo', 'receita.itens.subReceita.itens.insumo', 'regras'])
            ->get();

        $sugestoes = $this->autoPricing->gerarColecaoSugestoes($produtos);

        $ok = $sugestoes->where('status', 'ok')->count();
        $alerta = $sugestoes->where('status', 'alerta')->count();
        $bloqueado = $sugestoes->where('status', 'bloqueado')->count() + $sugestoes->where('status', 'erro')->count();

        $margemMedia = round((float) $sugestoes->avg('margem'), 2);
        $cmvMedio = round((float) $sugestoes->avg('cmv'), 2);
        $impacto = round((float) $sugestoes->sum('diferenca_preco'), 2);

        return [
            'kpis' => [
                'produtos_total' => $produtos->count(),
                'ok' => $ok,
                'alerta' => $alerta,
                'bloqueado' => $bloqueado,
                'margem_media' => $margemMedia,
                'cmv_medio' => $cmvMedio,
                'impacto_total_recomendado' => $impacto,
            ],
            'top_lucrativos' => $sugestoes->sortByDesc('margem')->take(5)->values(),
            'top_criticos' => $sugestoes->sortBy('margem')->take(5)->values(),
            'sugestoes_pendentes' => $sugestoes->whereIn('status', ['alerta', 'bloqueado'])->values(),
            'sugestoes' => $sugestoes,
        ];
    }

    private function vazio(): array
    {
        return [
            'kpis' => [
                'produtos_total' => 0,
                'ok' => 0,
                'alerta' => 0,
                'bloqueado' => 0,
                'margem_media' => 0,
                'cmv_medio' => 0,
                'impacto_total_recomendado' => 0,
            ],
            'top_lucrativos' => collect(),
            'top_criticos' => collect(),
            'sugestoes_pendentes' => collect(),
            'sugestoes' => collect(),
        ];
    }
}
