<?php

namespace App\Modules\AI\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesRecommendationService
{
    public function recommendations(int $empresaId): array
    {
        return Cache::remember("ai_recommendations_{$empresaId}", 120, function () use ($empresaId) {
            $items = [];
            $topProducts = $this->topProducts($empresaId);
            $cashRisk = app(ForecastService::class)->cashRisk($empresaId);

            if (! empty($topProducts)) {
                $items[] = [
                    'type' => 'produto',
                    'priority' => 'alta',
                    'title' => 'Aumentar foco nos produtos líderes',
                    'message' => 'Os produtos mais vendidos concentram a demanda. Priorize reposição, vitrine e oferta nesses itens para proteger margem.',
                    'data' => $topProducts,
                ];
            }

            if (($cashRisk['risk'] ?? 'baixo') !== 'baixo') {
                $items[] = [
                    'type' => 'financeiro',
                    'priority' => 'alta',
                    'title' => 'Reforçar recebimento e capital de giro',
                    'message' => 'A cobertura projetada dos próximos 30 dias está pressionada. Antecipe cobrança, renegocie vencimentos e reduza compras não essenciais.',
                    'data' => $cashRisk,
                ];
            }

            if (empty($items)) {
                $items[] = [
                    'type' => 'crescimento',
                    'priority' => 'media',
                    'title' => 'Operação estável para escalar',
                    'message' => 'A saúde operacional está consistente. A recomendação é aumentar tráfego comercial, campanhas e retenção de clientes.',
                    'data' => [],
                ];
            }

            return $items;
        });
    }

    private function topProducts(int $empresaId): array
    {
        if (! Schema::hasTable('venda_itens')) {
            return [];
        }

        $produtoColumn = $this->firstExistingColumn('venda_itens', ['produto_id']);
        $qtdColumn = $this->firstExistingColumn('venda_itens', ['quantidade', 'qtd']);
        $empresaJoin = Schema::hasTable('vendas') && Schema::hasColumn('vendas', 'empresa_id') && Schema::hasColumn('venda_itens', 'venda_id');

        if (! $produtoColumn || ! $qtdColumn || ! $empresaJoin) {
            return [];
        }

        return DB::table('venda_itens')
            ->join('vendas', 'vendas.id', '=', 'venda_itens.venda_id')
            ->selectRaw("{$produtoColumn} as produto_id, SUM({$qtdColumn}) as quantidade")
            ->where('vendas.empresa_id', $empresaId)
            ->groupBy($produtoColumn)
            ->orderByDesc('quantidade')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['produto_id' => (int) $row->produto_id, 'quantidade' => (float) $row->quantidade])
            ->all();
    }

    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }
}
