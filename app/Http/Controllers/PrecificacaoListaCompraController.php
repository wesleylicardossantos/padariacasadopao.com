<?php

namespace App\Http\Controllers;

use App\Models\PrecificacaoListaCompra;
use App\Models\PrecificacaoListaCompraItem;
use App\Support\PrecificacaoSchema;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class PrecificacaoListaCompraController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);
        $hasListas = PrecificacaoSchema::hasTable('precificacao_lista_compras');
        $hasItens = PrecificacaoSchema::hasTable('precificacao_lista_compras_itens');

        $listas = collect();
        $itens = collect();

        if ($hasListas) {
            $builder = PrecificacaoListaCompra::query();
            if (PrecificacaoSchema::hasColumn('precificacao_lista_compras', 'empresa_id')) {
                $builder->where('empresa_id', $empresaId);
            }
            $listas = $builder->latest('id')->limit(15)->get();
        }

        if ($hasItens) {
            $builderItens = PrecificacaoListaCompraItem::query();
            $itens = $builderItens->latest('id')->limit(20)->get();
        }

        $cards = [
            'listas_total' => $listas->count(),
            'itens_total' => $itens->count(),
            'custo_estimado' => round((float) $itens->sum('custo_estimado'), 2),
            'quantidade_total' => round((float) $itens->sum('quantidade'), 2),
        ];

        return view('precificacao.lista_compras.index', [
            'title' => 'Lista de Compras',
            'rotaAtiva' => 'Precificação',
            'estrutura' => ['listas' => $hasListas, 'itens' => $hasItens],
            'cards' => $cards,
            'listas' => $listas,
            'itens' => $itens,
        ]);
    }
}
