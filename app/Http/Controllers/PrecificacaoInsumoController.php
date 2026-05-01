<?php

namespace App\Http\Controllers;

use App\Models\PrecificacaoInsumo;
use App\Support\PrecificacaoSchema;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class PrecificacaoInsumoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);
        $estruturaOk = PrecificacaoSchema::hasTable('precificacao_insumos');
        $colunas = [
            'empresa_id' => PrecificacaoSchema::hasColumn('precificacao_insumos', 'empresa_id'),
            'produto_legado_id' => PrecificacaoSchema::hasColumn('precificacao_insumos', 'produto_legado_id'),
            'categoria' => PrecificacaoSchema::hasColumn('precificacao_insumos', 'categoria'),
            'unidade' => PrecificacaoSchema::hasColumn('precificacao_insumos', 'unidade'),
            'custo_unitario' => PrecificacaoSchema::hasColumn('precificacao_insumos', 'custo_unitario'),
            'ativo' => PrecificacaoSchema::hasColumn('precificacao_insumos', 'ativo'),
        ];

        $insumos = collect();
        if ($estruturaOk) {
            $query = PrecificacaoInsumo::query();
            if ($colunas['empresa_id'] && $empresaId) {
                $query->where('empresa_id', $empresaId);
            }
            $insumos = $query->orderBy('nome')->limit(200)->get();
        }

        return view('precificacao.insumos.index', [
            'title' => 'Insumos',
            'estruturaOk' => $estruturaOk,
            'colunas' => $colunas,
            'insumos' => $insumos,
        ]);
    }
}
