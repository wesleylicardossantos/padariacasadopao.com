<?php

namespace App\Http\Controllers;

use App\Models\PrecificacaoProducao;
use App\Models\PrecificacaoProducaoItem;
use App\Support\PrecificacaoSchema;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class PrecificacaoProducaoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);
        $hasProducoes = PrecificacaoSchema::hasTable('precificacao_producao');
        $hasItens = PrecificacaoSchema::hasTable('precificacao_producao_itens');

        $producoes = collect();
        $itens = collect();

        if ($hasProducoes) {
            $builder = PrecificacaoProducao::query();
            if (PrecificacaoSchema::hasColumn('precificacao_producao', 'empresa_id')) {
                $builder->where('empresa_id', $empresaId);
            }
            $producoes = $builder->latest('id')->limit(15)->get();
        }

        if ($hasItens) {
            $itens = PrecificacaoProducaoItem::query()->latest('id')->limit(20)->get();
        }

        $cards = [
            'producoes_total' => $producoes->count(),
            'concluidas' => $producoes->filter(fn ($item) => strtolower((string) ($item->status ?? '')) === 'concluida')->count(),
            'custo_real' => round((float) $producoes->sum('custo_real'), 2),
            'custo_teorico' => round((float) $producoes->sum('custo_teorico'), 2),
        ];

        return view('precificacao.producao.index', [
            'title' => 'Produção',
            'rotaAtiva' => 'Precificação',
            'estrutura' => ['producoes' => $hasProducoes, 'itens' => $hasItens],
            'cards' => $cards,
            'producoes' => $producoes,
            'itens' => $itens,
        ]);
    }
}
