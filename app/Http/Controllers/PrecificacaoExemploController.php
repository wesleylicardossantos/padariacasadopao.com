<?php

namespace App\Http\Controllers;

use App\Models\PrecificacaoProduto;
use App\Support\PrecificacaoSchema;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class PrecificacaoExemploController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);

        $produtos = collect();
        if (PrecificacaoSchema::hasTable('precificacao_produtos')) {
            $produtos = PrecificacaoProduto::query()
                ->where('empresa_id', $empresaId)
                ->latest('id')
                ->limit(12)
                ->get();
        }

        $exemplos = [
            ['nome' => 'BROA DE MILHO', 'grupo' => 'Padaria', 'rota' => 'precificacao.receitas.index'],
            ['nome' => 'PÃO FRANCÊS', 'grupo' => 'Padaria', 'rota' => 'precificacao.receitas.index'],
            ['nome' => 'COXINHA PROFISSIONAL', 'grupo' => 'Salgados', 'rota' => 'precificacao.receitas.index'],
            ['nome' => 'BOLO DE CHOCOLATE FATIA', 'grupo' => 'Confeitaria', 'rota' => 'precificacao.receitas.index'],
            ['nome' => 'BISCOITO CASEIRO', 'grupo' => 'Padaria', 'rota' => 'precificacao.receitas.index'],
        ];

        return view('precificacao.exemplos.index', [
            'title' => 'Exemplos de Precificação',
            'rotaAtiva' => 'Precificação',
            'produtos' => $produtos,
            'exemplos' => $exemplos,
        ]);
    }
}
