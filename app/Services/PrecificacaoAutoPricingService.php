<?php

namespace App\Services;

use App\Models\PrecificacaoProduto;
use App\Models\PrecificacaoReceita;
use App\Models\PrecificacaoRegra;
use App\Models\Produto;
use App\Support\PrecificacaoSchema;

class PrecificacaoAutoPricingService
{
    public function __construct(private PrecificacaoValidacaoService $validacao)
    {
    }

    public function gerarSugestao(PrecificacaoProduto $produto): array
    {
        $receita = $produto->receita;
        $possuiReceita = (bool) $receita;

        $detalheReceita = $possuiReceita
            ? $this->calcularReceita($receita)
            : ['custo_total' => 0.0, 'insumos_sem_custo' => 0];

        $custoUnitario = (float) ($detalheReceita['custo_total'] ?? 0);
        $insumosSemCusto = (int) ($detalheReceita['insumos_sem_custo'] ?? 0);

        $regras = $this->regrasDoProduto($produto->id);
        $margemMinima = (float) ($regras['margem'] ?? 30);
        $cmvMaximo = (float) ($regras['cmv_maximo'] ?? 40);
        $arredondamento = (float) ($regras['arredondamento'] ?? 0.90);
        $despesasPercentual = (float) ($regras['acrescimo'] ?? 0);
        $margemAlvo = max($margemMinima, 40.0);

        $precoSugerido = 0.0;
        if ($custoUnitario > 0) {
            $precoSugerido = $custoUnitario / max(0.0001, (1 - (($despesasPercentual + $margemAlvo) / 100)));
            $precoSugerido = $this->arredondarComercial($precoSugerido, $arredondamento);
        }

        $produtoLegado = $this->resolverProdutoLegado($produto);
        $precoAtual = (float) ($produtoLegado->valor_venda ?? $produto->preco_sugerido ?? 0);
        $custoAtual = (float) ($produtoLegado->valor_compra ?? $produto->custo_total ?? 0);

        $validacao = $this->validacao->validar([
            'custo_total' => $custoUnitario,
            'preco_sugerido' => $precoSugerido,
            'despesas_percentual' => $despesasPercentual,
            'margem_minima' => $margemMinima,
            'margem_alvo' => $margemAlvo,
            'cmv_maximo' => $cmvMaximo,
            'possui_receita' => $possuiReceita,
            'possui_vinculo' => (bool) $produtoLegado,
            'insumos_sem_custo' => $insumosSemCusto,
        ]);

        return array_merge($validacao, [
            'produto' => $produto,
            'produto_legado' => $produtoLegado,
            'custo_total' => round($custoUnitario, 4),
            'custo_atual' => round($custoAtual, 4),
            'preco_atual' => round($precoAtual, 4),
            'preco_sugerido' => round($precoSugerido, 4),
            'margem_minima' => $margemMinima,
            'margem_alvo' => $margemAlvo,
            'cmv_maximo' => $cmvMaximo,
            'despesas_percentual' => $despesasPercentual,
            'insumos_sem_custo' => $insumosSemCusto,
            'diferenca_preco' => round($precoSugerido - $precoAtual, 4),
        ]);
    }

    public function gerarColecaoSugestoes($produtos)
    {
        return collect($produtos)->map(function ($produto) {
            try {
                return $this->gerarSugestao($produto);
            } catch (\Throwable $e) {
                return [
                    'produto' => $produto,
                    'status' => 'erro',
                    'margem' => 0,
                    'cmv' => 0,
                    'preco_minimo' => 0,
                    'custo_total' => 0,
                    'preco_atual' => 0,
                    'preco_sugerido' => 0,
                    'bloqueios' => ['Falha ao calcular a sugestão: ' . $e->getMessage()],
                    'alertas' => [],
                ];
            }
        });
    }

    private function calcularReceita(?PrecificacaoReceita $receita, array $visitadas = []): array
    {
        if (! $receita) {
            return ['custo_total' => 0.0, 'insumos_sem_custo' => 1];
        }

        if (in_array($receita->id, $visitadas, true)) {
            return ['custo_total' => 0.0, 'insumos_sem_custo' => 1];
        }

        $visitadas[] = $receita->id;
        $itens = $receita->relationLoaded('itens') ? $receita->itens : $receita->itens()->with(['insumo', 'subReceita.itens.insumo', 'subReceita.itens.subReceita'])->get();

        $insumosSemCusto = 0;
        $custoItens = 0.0;

        foreach ($itens as $item) {
            $custo = 0.0;

            if (! empty($item->sub_receita_id)) {
                $subReceita = $item->relationLoaded('subReceita') ? $item->subReceita : $item->subReceita()->with(['itens.insumo', 'itens.subReceita'])->first();
                $sub = $this->calcularReceita($subReceita, $visitadas);
                $custo = ((float) ($item->quantidade ?? 0)) * (float) ($sub['custo_total'] ?? 0);
                $insumosSemCusto += (int) ($sub['insumos_sem_custo'] ?? 0);
            } else {
                $custo = (float) ($item->custo_total ?? 0);
                $custoUnitario = (float) ($item->custo_unitario ?? 0);
                if ($custo <= 0 && $custoUnitario <= 0 && $item->relationLoaded('insumo') && $item->insumo) {
                    $custoUnitario = (float) ($item->insumo->custo_unitario ?? 0);
                }
                if ($custo <= 0) {
                    $custo = ((float) ($item->quantidade ?? 0)) * $custoUnitario;
                }
                if ($custo <= 0) {
                    $insumosSemCusto++;
                }
            }

            $custoItens += max(0, $custo);
        }

        $custoBase = $custoItens
            + (float) ($receita->custo_mao_obra ?? 0)
            + (float) ($receita->custo_indireto ?? 0)
            + (float) ($receita->custo_embalagem ?? 0);

        $perda = (float) ($receita->perda ?? 0);
        if ($custoBase > 0 && $perda > 0 && $perda < 100) {
            $custoBase = $custoBase / (1 - ($perda / 100));
        }

        $rendimento = max(1, (float) ($receita->rendimento ?? 1));
        return [
            'custo_total' => $custoBase / $rendimento,
            'insumos_sem_custo' => $insumosSemCusto,
        ];
    }

    private function resolverProdutoLegado(PrecificacaoProduto $produto): ?Produto
    {
        if (PrecificacaoSchema::hasColumn('precificacao_produtos', 'produto_legado_id') && ! empty($produto->produto_legado_id)) {
            return Produto::find($produto->produto_legado_id);
        }

        return Produto::where('empresa_id', $produto->empresa_id)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower((string) $produto->nome)])
            ->first();
    }

    private function regrasDoProduto(int $precificacaoId): array
    {
        if (! PrecificacaoSchema::hasTable('precificacao_regras')) {
            return [];
        }

        return PrecificacaoRegra::query()
            ->where('precificacao_id', $precificacaoId)
            ->where('ativo', 1)
            ->orderBy('prioridade')
            ->get()
            ->pluck('valor', 'tipo')
            ->toArray();
    }

    private function arredondarComercial(float $valor, float $final): float
    {
        if ($valor <= 0) {
            return 0.0;
        }

        $inteiro = floor($valor);
        $centavos = $valor - $inteiro;
        $alvo = max(0, min(0.99, $final));

        if ($centavos <= $alvo) {
            return round($inteiro + $alvo, 2);
        }

        return round(($inteiro + 1) + $alvo, 2);
    }
}
