<?php

namespace App\Http\Controllers;

use App\Models\PrecificacaoProduto;
use App\Services\PrecificacaoDashboardExecutivoService;
use App\Services\PrecificacaoPadariaKitService;
use App\Support\PrecificacaoSchema;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class PrecificacaoController extends Controller
{
    public function __construct(
        private PrecificacaoDashboardExecutivoService $dashboard,
        private PrecificacaoPadariaKitService $kitService
    ) {
    }


    public function instalarKitPadaria(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);

        try {
            $resumo = $this->kitService->instalar($empresaId);

            return redirect()
                ->route('precificacao.index')
                ->with('success', 'Kit avançado de padaria implantado com sucesso. Insumos: ' . $resumo['insumos'] . ', fichas: ' . $resumo['receitas'] . ', produtos: ' . $resumo['produtos'] . '.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('precificacao.index')
                ->with('error', 'Falha ao implantar o kit avançado de padaria: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);
        $dados = $this->dashboard->montar($empresaId);

        $estrutura = [
            'produtos' => PrecificacaoSchema::hasTable('precificacao_produtos'),
            'receitas' => PrecificacaoSchema::hasTable('precificacao_receitas'),
            'regras' => PrecificacaoSchema::hasTable('precificacao_regras'),
            'historico' => PrecificacaoSchema::hasTable('precificacao_historico'),
        ];

        $ultimosProdutos = collect();
        if ($estrutura['produtos']) {
            $ultimosProdutos = PrecificacaoProduto::query()
                ->where('empresa_id', $empresaId)
                ->latest('id')
                ->limit(8)
                ->get();
        }

        $itensCardapio = collect($dados['sugestoes'])->map(function ($item) {
            $produto = $item['produto'] ?? null;
            $receita = $produto?->receita;
            $precoPublicado = (float) ($item['preco_atual'] ?? 0);
            $precoSugerido = (float) ($item['preco_sugerido'] ?? 0);
            $precoExibicao = $precoPublicado > 0 ? $precoPublicado : $precoSugerido;
            $lucroBruto = $precoExibicao - (float) ($item['custo_total'] ?? 0);

            return [
                'produto' => $produto,
                'categoria' => $receita?->nome ?: 'Sem categoria',
                'canal' => 'Canal padrão',
                'preco' => $precoExibicao,
                'preco_sugerido' => $precoSugerido,
                'custo_total' => (float) ($item['custo_total'] ?? 0),
                'cmv' => (float) ($item['cmv'] ?? 0),
                'despesas_percentual' => (float) ($item['despesas_percentual'] ?? 0),
                'lucro_bruto' => $lucroBruto,
                'status' => $item['status'] ?? 'erro',
                'bloqueios' => $item['bloqueios'] ?? [],
                'alertas' => $item['alertas'] ?? [],
            ];
        })->values();

        $categorias = $itensCardapio->pluck('categoria')->filter()->unique()->values();
        $canais = $itensCardapio->pluck('canal')->filter()->unique()->values();

        return view('precificacao.index', [
            'title' => 'Painel de Precificação',
            'rotaAtiva' => 'Precificação',
            'dashboard' => $dados,
            'estrutura' => $estrutura,
            'ultimosProdutos' => $ultimosProdutos,
            'itensCardapio' => $itensCardapio,
            'categorias' => $categorias,
            'canais' => $canais,
        ]);
    }
}
