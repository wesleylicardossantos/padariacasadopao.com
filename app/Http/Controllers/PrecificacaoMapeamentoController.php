<?php

namespace App\Http\Controllers;

use App\Models\PrecificacaoInsumo;
use App\Models\Produto;
use App\Support\PrecificacaoSchema;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class PrecificacaoMapeamentoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);
        $estruturaOk = PrecificacaoSchema::hasTable('precificacao_insumos');
        $produtoIdOk = PrecificacaoSchema::hasColumn('precificacao_insumos', 'produto_legado_id');
        $empresaColOk = PrecificacaoSchema::hasColumn('precificacao_insumos', 'empresa_id');

        $insumos = collect();
        $produtosMap = collect();
        if ($estruturaOk) {
            $query = PrecificacaoInsumo::query();
            if ($empresaColOk && $empresaId) {
                $query->where('empresa_id', $empresaId);
            }
            $insumos = $query->orderBy('nome')->limit(250)->get();

            $ids = $produtoIdOk ? $insumos->pluck('produto_legado_id')->filter()->unique()->values() : collect();
            if ($ids->isNotEmpty()) {
                $produtosMap = Produto::query()->whereIn('id', $ids)->get()->keyBy('id');
            }
        }

        $vinculados = $produtoIdOk ? $insumos->filter(function ($insumo) {
            return !empty($insumo->produto_legado_id);
        })->count() : 0;

        return view('precificacao.mapeamentos.index', [
            'title' => 'Mapeamentos',
            'estruturaOk' => $estruturaOk,
            'produtoIdOk' => $produtoIdOk,
            'insumos' => $insumos,
            'produtosMap' => $produtosMap,
            'vinculados' => $vinculados,
        ]);
    }
}
