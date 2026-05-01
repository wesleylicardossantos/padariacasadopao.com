<?php

namespace App\Http\Controllers;

use App\Models\PrecificacaoHistorico;
use App\Models\PrecificacaoProduto;
use App\Models\Produto;
use App\Services\PrecificacaoAutoPricingService;
use App\Support\PrecificacaoSchema;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrecificacaoSugestaoController extends Controller
{
    public function __construct(private PrecificacaoAutoPricingService $autoPricing)
    {
    }

    public function index(Request $request)
    {
        $empresaId = TenantContext::empresaId($request);
        $produtos = collect();
        if (PrecificacaoSchema::hasTable('precificacao_produtos')) {
            $produtos = PrecificacaoProduto::query()
                ->where('empresa_id', $empresaId)
                ->with(['receita.itens', 'regras'])
                ->orderBy('nome')
                ->get();
        }

        $sugestoes = $this->autoPricing->gerarColecaoSugestoes($produtos)->values();
        $resumo = [
            'total' => $sugestoes->count(),
            'ok' => $sugestoes->where('status', 'ok')->count(),
            'alerta' => $sugestoes->where('status', 'alerta')->count(),
            'bloqueado' => $sugestoes->whereIn('status', ['bloqueado', 'erro'])->count(),
        ];

        return view('precificacao.sugestoes.index', [
            'title' => 'Sugestões de Preço',
            'rotaAtiva' => 'Precificação',
            'sugestoes' => $sugestoes,
            'resumo' => $resumo,
        ]);
    }

    public function aprovar(Request $request, $id)
    {
        $request->validate([
            'justificativa' => ['nullable', 'string', 'max:500'],
        ]);

        $empresaId = TenantContext::empresaId($request);
        $userId = TenantContext::userId($request);

        $produto = PrecificacaoProduto::query()
            ->where('empresa_id', $empresaId)
            ->with(['receita.itens', 'regras'])
            ->findOrFail($id);

        $sugestao = $this->autoPricing->gerarSugestao($produto);

        if ($sugestao['status'] === 'bloqueado' || $sugestao['status'] === 'erro') {
            return redirect()->back()->with('flash_erro', 'Publicação bloqueada: ' . implode(' | ', $sugestao['bloqueios']));
        }

        if ($sugestao['status'] === 'alerta' && ! $request->filled('justificativa')) {
            return redirect()->back()->with('flash_erro', 'Informe uma justificativa para aprovar uma sugestão em alerta.');
        }

        DB::beginTransaction();
        try {
            $precoAntigo = (float) ($produto->preco_sugerido ?? 0);
            $produto->custo_total = $sugestao['custo_total'];
            $produto->preco_sugerido = $sugestao['preco_sugerido'];
            $produto->lucro_bruto = round($sugestao['preco_sugerido'] - $sugestao['custo_total'], 4);
            $produto->cmv = $sugestao['cmv'];
            $produto->save();

            $produtoLegado = $sugestao['produto_legado'];
            if ($produtoLegado instanceof Produto) {
                $produtoLegado->valor_compra = $sugestao['custo_total'];
                $produtoLegado->valor_venda = $sugestao['preco_sugerido'];
                $produtoLegado->percentual_lucro = $sugestao['margem'];
                $produtoLegado->save();
            }

            if (PrecificacaoSchema::hasTable('precificacao_historico')) {
                $payload = [
                    'preco_antigo' => $precoAntigo,
                    'preco_novo' => $sugestao['preco_sugerido'],
                ];

                if (PrecificacaoSchema::hasColumn('precificacao_historico', 'precificacao_id')) {
                    $payload['precificacao_id'] = $produto->id;
                }
                if (PrecificacaoSchema::hasColumn('precificacao_historico', 'produto_id')) {
                    $payload['produto_id'] = $produtoLegado?->id ?: $produto->id;
                }
                if (PrecificacaoSchema::hasColumn('precificacao_historico', 'alterado_por')) {
                    $payload['alterado_por'] = $userId;
                } elseif (PrecificacaoSchema::hasColumn('precificacao_historico', 'usuario_id')) {
                    $payload['usuario_id'] = $userId;
                }

                PrecificacaoHistorico::create($payload);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if (function_exists('__saveLogError')) {
                __saveLogError($e, $empresaId);
            }

            return redirect()->back()->with('flash_erro', 'Erro ao aprovar sugestão: ' . $e->getMessage());
        }

        return redirect()->back()->with('flash_sucesso', 'Sugestão aprovada e publicada com segurança.');
    }
}
