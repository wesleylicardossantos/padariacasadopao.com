<?php

namespace App\Http\Controllers;

use App\Models\PrecificacaoCanalVenda;
use App\Support\PrecificacaoSchema;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class PrecificacaoCanalController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);
        $estruturaOk = PrecificacaoSchema::hasTable('precificacao_canais_venda');
        $query = collect();

        if ($estruturaOk) {
            $builder = PrecificacaoCanalVenda::query();
            if (PrecificacaoSchema::hasColumn('precificacao_canais_venda', 'empresa_id')) {
                $builder->where('empresa_id', $empresaId);
            }
            $query = $builder->orderBy('nome')->get();
        }

        $cards = [
            'total' => $query->count(),
            'ativos' => $query->filter(fn ($item) => (int) ($item->ativo ?? 1) === 1)->count(),
            'taxa_media' => round((float) $query->avg('taxa_percentual'), 2),
            'comissao_media' => round((float) $query->avg('comissao'), 2),
        ];

        return view('precificacao.canais.index', [
            'title' => 'Canais de Venda',
            'rotaAtiva' => 'Precificação',
            'estruturaOk' => $estruturaOk,
            'cards' => $cards,
            'canais' => $query,
        ]);
    }
}
