<?php

namespace App\Services;

use App\Models\PrecificacaoInsumo;
use App\Models\PrecificacaoProduto;
use App\Models\PrecificacaoReceita;
use App\Models\PrecificacaoReceitaItem;
use App\Models\PrecificacaoRegra;
use App\Models\Produto;
use App\Support\PrecificacaoSchema;
use Illuminate\Support\Facades\DB;

class PrecificacaoPadariaKitService
{
    public function instalar(int $empresaId): array
    {
        $this->validarEstruturaMinima();

        return DB::transaction(function () use ($empresaId) {
            $insumos = $this->sincronizarInsumos($empresaId);
            $receitas = $this->sincronizarReceitas($empresaId, $insumos);
            $produtos = $this->sincronizarProdutos($empresaId, $receitas);
            $regras = $this->sincronizarRegras($produtos);

            return [
                'insumos' => count($insumos),
                'receitas' => count($receitas),
                'produtos' => count($produtos),
                'regras' => $regras,
            ];
        });
    }

    private function validarEstruturaMinima(): void
    {
        $obrigatorias = [
            'precificacao_insumos',
            'precificacao_receitas',
            'precificacao_receita_itens',
            'precificacao_produtos',
            'precificacao_regras',
        ];

        foreach ($obrigatorias as $tabela) {
            if (! PrecificacaoSchema::hasTable($tabela)) {
                throw new \RuntimeException("Tabela obrigatória ausente: {$tabela}");
            }
        }
    }

    private function sincronizarInsumos(int $empresaId): array
    {
        $itens = [
            ['nome' => 'FARINHA DE MILHO', 'categoria' => 'Grãos e farinhas', 'unidade' => 'g', 'custo_unitario' => 0.0050],
            ['nome' => 'AÇÚCAR', 'categoria' => 'Secos', 'unidade' => 'g', 'custo_unitario' => 0.0040],
            ['nome' => 'OVO', 'categoria' => 'Frios', 'unidade' => 'un', 'custo_unitario' => 0.8000],
            ['nome' => 'LEITE', 'categoria' => 'Laticínios', 'unidade' => 'ml', 'custo_unitario' => 0.0030],
            ['nome' => 'FERMENTO', 'categoria' => 'Secos', 'unidade' => 'g', 'custo_unitario' => 0.0200],
            ['nome' => 'EMBALAGEM PADRÃO', 'categoria' => 'Embalagens', 'unidade' => 'un', 'custo_unitario' => 0.2000],
            ['nome' => 'FARINHA DE TRIGO', 'categoria' => 'Grãos e farinhas', 'unidade' => 'g', 'custo_unitario' => 0.0045],
            ['nome' => 'ÁGUA', 'categoria' => 'Base', 'unidade' => 'ml', 'custo_unitario' => 0.0005],
            ['nome' => 'SAL', 'categoria' => 'Secos', 'unidade' => 'g', 'custo_unitario' => 0.0020],
            ['nome' => 'ÓLEO', 'categoria' => 'Óleos', 'unidade' => 'ml', 'custo_unitario' => 0.0060],
            ['nome' => 'FRANGO', 'categoria' => 'Carnes', 'unidade' => 'g', 'custo_unitario' => 0.0100],
            ['nome' => 'TEMPEROS', 'categoria' => 'Secos', 'unidade' => 'g', 'custo_unitario' => 0.0100],
            ['nome' => 'LEITE CONDENSADO', 'categoria' => 'Doces', 'unidade' => 'un', 'custo_unitario' => 5.0000],
            ['nome' => 'CHOCOLATE', 'categoria' => 'Doces', 'unidade' => 'g', 'custo_unitario' => 0.0200],
            ['nome' => 'MANTEIGA', 'categoria' => 'Laticínios', 'unidade' => 'g', 'custo_unitario' => 0.0150],
            ['nome' => 'EMBALAGEM FATIA', 'categoria' => 'Embalagens', 'unidade' => 'un', 'custo_unitario' => 0.3000],
        ];

        $resultado = [];
        foreach ($itens as $item) {
            $attributes = $this->filtrarColunas('precificacao_insumos', [
                'empresa_id' => $empresaId,
                'nome' => $item['nome'],
                'categoria' => $item['categoria'],
                'unidade' => $item['unidade'],
                'unidade_compra' => $item['unidade'],
                'unidade_uso' => $item['unidade'],
                'fator_conversao' => 1,
                'fator_perda' => 0,
                'quantidade_embalagem' => 1,
                'custo_embalagem' => 0,
                'custo_unitario' => $item['custo_unitario'],
                'custo_unitario_base' => $item['custo_unitario'],
                'fornecedor' => 'KIT PADARIA',
                'ativo' => 1,
            ]);

            $query = PrecificacaoInsumo::query()->where('nome', $item['nome']);
            if (PrecificacaoSchema::hasColumn('precificacao_insumos', 'empresa_id')) {
                $query->where('empresa_id', $empresaId);
            }

            $model = $query->first() ?: new PrecificacaoInsumo();
            $model->fill($attributes);
            $model->save();

            $resultado[$item['nome']] = $model;
        }

        return $resultado;
    }

    private function sincronizarReceitas(int $empresaId, array $insumos): array
    {
        $receitas = [
            [
                'nome' => 'MASSA BASE PÃO',
                'rendimento' => 100,
                'unidade_rendimento' => 'UN',
                'perda' => 5,
                'status' => 'ativo',
                'custo_mao_obra' => 0,
                'custo_indireto' => 0,
                'custo_embalagem' => 0,
                'itens' => [
                    ['tipo' => 'insumo', 'nome' => 'FARINHA DE TRIGO', 'quantidade' => 4000],
                    ['tipo' => 'insumo', 'nome' => 'ÁGUA', 'quantidade' => 2400],
                    ['tipo' => 'insumo', 'nome' => 'FERMENTO', 'quantidade' => 80],
                    ['tipo' => 'insumo', 'nome' => 'SAL', 'quantidade' => 60],
                ],
            ],
            [
                'nome' => 'MASSA BASE SALGADOS',
                'rendimento' => 50,
                'unidade_rendimento' => 'UN',
                'perda' => 5,
                'status' => 'ativo',
                'custo_mao_obra' => 0,
                'custo_indireto' => 0,
                'custo_embalagem' => 0,
                'itens' => [
                    ['tipo' => 'insumo', 'nome' => 'FARINHA DE TRIGO', 'quantidade' => 2000],
                    ['tipo' => 'insumo', 'nome' => 'ÁGUA', 'quantidade' => 1500],
                    ['tipo' => 'insumo', 'nome' => 'ÓLEO', 'quantidade' => 200],
                    ['tipo' => 'insumo', 'nome' => 'SAL', 'quantidade' => 40],
                ],
            ],
            [
                'nome' => 'RECHEIO FRANGO PADRÃO',
                'rendimento' => 50,
                'unidade_rendimento' => 'UN',
                'perda' => 0,
                'status' => 'ativo',
                'custo_mao_obra' => 0,
                'custo_indireto' => 0,
                'custo_embalagem' => 0,
                'itens' => [
                    ['tipo' => 'insumo', 'nome' => 'FRANGO', 'quantidade' => 2000],
                    ['tipo' => 'insumo', 'nome' => 'TEMPEROS', 'quantidade' => 200],
                ],
            ],
            [
                'nome' => 'RECHEIO BRIGADEIRO PADRÃO',
                'rendimento' => 40,
                'unidade_rendimento' => 'UN',
                'perda' => 0,
                'status' => 'ativo',
                'custo_mao_obra' => 0,
                'custo_indireto' => 0,
                'custo_embalagem' => 0,
                'itens' => [
                    ['tipo' => 'insumo', 'nome' => 'LEITE CONDENSADO', 'quantidade' => 2],
                    ['tipo' => 'insumo', 'nome' => 'CHOCOLATE', 'quantidade' => 200],
                    ['tipo' => 'insumo', 'nome' => 'MANTEIGA', 'quantidade' => 50],
                ],
            ],
            [
                'nome' => 'BROA DE MILHO',
                'rendimento' => 20,
                'unidade_rendimento' => 'UN',
                'perda' => 5,
                'status' => 'ativo',
                'custo_mao_obra' => 0,
                'custo_indireto' => 0,
                'custo_embalagem' => 0,
                'itens' => [
                    ['tipo' => 'insumo', 'nome' => 'FARINHA DE MILHO', 'quantidade' => 1000],
                    ['tipo' => 'insumo', 'nome' => 'AÇÚCAR', 'quantidade' => 300],
                    ['tipo' => 'insumo', 'nome' => 'OVO', 'quantidade' => 5],
                    ['tipo' => 'insumo', 'nome' => 'LEITE', 'quantidade' => 500],
                    ['tipo' => 'insumo', 'nome' => 'FERMENTO', 'quantidade' => 20],
                    ['tipo' => 'insumo', 'nome' => 'EMBALAGEM PADRÃO', 'quantidade' => 20],
                ],
            ],
            [
                'nome' => 'PÃO FRANCÊS',
                'rendimento' => 50,
                'unidade_rendimento' => 'UN',
                'perda' => 5,
                'status' => 'ativo',
                'custo_mao_obra' => 0,
                'custo_indireto' => 0,
                'custo_embalagem' => 0,
                'itens' => [
                    ['tipo' => 'sub_receita', 'nome' => 'MASSA BASE PÃO', 'quantidade' => 50],
                ],
            ],
            [
                'nome' => 'COXINHA PROFISSIONAL',
                'rendimento' => 30,
                'unidade_rendimento' => 'UN',
                'perda' => 5,
                'status' => 'ativo',
                'custo_mao_obra' => 0,
                'custo_indireto' => 0,
                'custo_embalagem' => 0,
                'itens' => [
                    ['tipo' => 'sub_receita', 'nome' => 'MASSA BASE SALGADOS', 'quantidade' => 30],
                    ['tipo' => 'sub_receita', 'nome' => 'RECHEIO FRANGO PADRÃO', 'quantidade' => 30],
                    ['tipo' => 'insumo', 'nome' => 'EMBALAGEM PADRÃO', 'quantidade' => 30],
                ],
            ],
            [
                'nome' => 'BOLO DE CHOCOLATE FATIA',
                'rendimento' => 10,
                'unidade_rendimento' => 'UN',
                'perda' => 5,
                'status' => 'ativo',
                'custo_mao_obra' => 0,
                'custo_indireto' => 0,
                'custo_embalagem' => 0,
                'itens' => [
                    ['tipo' => 'insumo', 'nome' => 'FARINHA DE TRIGO', 'quantidade' => 300],
                    ['tipo' => 'insumo', 'nome' => 'AÇÚCAR', 'quantidade' => 200],
                    ['tipo' => 'insumo', 'nome' => 'CHOCOLATE', 'quantidade' => 200],
                    ['tipo' => 'insumo', 'nome' => 'OVO', 'quantidade' => 4],
                    ['tipo' => 'insumo', 'nome' => 'LEITE', 'quantidade' => 200],
                    ['tipo' => 'insumo', 'nome' => 'MANTEIGA', 'quantidade' => 100],
                    ['tipo' => 'insumo', 'nome' => 'EMBALAGEM FATIA', 'quantidade' => 10],
                ],
            ],
            [
                'nome' => 'BISCOITO CASEIRO',
                'rendimento' => 40,
                'unidade_rendimento' => 'UN',
                'perda' => 5,
                'status' => 'ativo',
                'custo_mao_obra' => 0,
                'custo_indireto' => 0,
                'custo_embalagem' => 0,
                'itens' => [
                    ['tipo' => 'insumo', 'nome' => 'FARINHA DE TRIGO', 'quantidade' => 800],
                    ['tipo' => 'insumo', 'nome' => 'AÇÚCAR', 'quantidade' => 400],
                    ['tipo' => 'insumo', 'nome' => 'MANTEIGA', 'quantidade' => 200],
                    ['tipo' => 'insumo', 'nome' => 'OVO', 'quantidade' => 3],
                ],
            ],
        ];

        $resultado = [];
        foreach ($receitas as $data) {
            $attrs = $this->filtrarColunas('precificacao_receitas', [
                'empresa_id' => $empresaId,
                'nome' => $data['nome'],
                'rendimento' => $data['rendimento'],
                'unidade_rendimento' => $data['unidade_rendimento'],
                'custo_mao_obra' => $data['custo_mao_obra'],
                'custo_indireto' => $data['custo_indireto'],
                'custo_embalagem' => $data['custo_embalagem'],
                'perda' => $data['perda'],
                'status' => $data['status'],
            ]);

            $query = PrecificacaoReceita::query()->where('nome', $data['nome']);
            if (PrecificacaoSchema::hasColumn('precificacao_receitas', 'empresa_id')) {
                $query->where('empresa_id', $empresaId);
            }

            $model = $query->first() ?: new PrecificacaoReceita();
            $model->fill($attrs);
            $model->save();
            $resultado[$data['nome']] = $model;
        }

        foreach ($receitas as $data) {
            $receita = $resultado[$data['nome']];
            PrecificacaoReceitaItem::query()->where('receita_id', $receita->id)->delete();

            foreach ($data['itens'] as $item) {
                $payload = [
                    'receita_id' => $receita->id,
                    'insumo_id' => null,
                    'sub_receita_id' => null,
                    'quantidade' => $item['quantidade'],
                    'custo_unitario' => 0,
                    'custo_total' => 0,
                ];

                if ($item['tipo'] === 'insumo') {
                    $insumo = $insumos[$item['nome']] ?? null;
                    if (! $insumo) {
                        continue;
                    }
                    $payload['insumo_id'] = $insumo->id;
                    $payload['custo_unitario'] = (float) ($insumo->custo_unitario ?? 0);
                    $payload['custo_total'] = round($payload['quantidade'] * $payload['custo_unitario'], 4);
                } else {
                    $sub = $resultado[$item['nome']] ?? null;
                    if (! $sub) {
                        continue;
                    }
                    $payload['sub_receita_id'] = $sub->id;
                }

                $payload = $this->filtrarColunas('precificacao_receita_itens', $payload);
                $itemModel = new PrecificacaoReceitaItem();
                $itemModel->fill($payload);
                $itemModel->save();
            }
        }

        return $resultado;
    }

    private function sincronizarProdutos(int $empresaId, array $receitas): array
    {
        $finais = [
            'BROA DE MILHO',
            'PÃO FRANCÊS',
            'COXINHA PROFISSIONAL',
            'BOLO DE CHOCOLATE FATIA',
            'BISCOITO CASEIRO',
        ];

        $resultado = [];
        foreach ($finais as $nome) {
            $receita = $receitas[$nome] ?? null;
            if (! $receita) {
                continue;
            }

            $produtoLegadoId = $this->resolverProdutoLegadoId($empresaId, $nome);
            $attrs = $this->filtrarColunas('precificacao_produtos', [
                'empresa_id' => $empresaId,
                'receita_id' => $receita->id,
                'nome' => $nome,
                'custo_total' => 0,
                'preco_sugerido' => 0,
                'lucro_bruto' => 0,
                'cmv' => 0,
                'produto_legado_id' => $produtoLegadoId,
            ]);

            $query = PrecificacaoProduto::query()->where('nome', $nome);
            if (PrecificacaoSchema::hasColumn('precificacao_produtos', 'empresa_id')) {
                $query->where('empresa_id', $empresaId);
            }

            $model = $query->first() ?: new PrecificacaoProduto();
            $model->fill($attrs);
            $model->save();
            $resultado[$nome] = $model;
        }

        return $resultado;
    }

    private function sincronizarRegras(array $produtos): int
    {
        $count = 0;
        foreach ($produtos as $produto) {
            PrecificacaoRegra::query()->where('precificacao_id', $produto->id)->delete();
            $regras = [
                ['tipo' => 'margem', 'valor' => 30.0000, 'prioridade' => 1],
                ['tipo' => 'cmv_maximo', 'valor' => 40.0000, 'prioridade' => 2],
                ['tipo' => 'arredondamento', 'valor' => 0.9000, 'prioridade' => 3],
                ['tipo' => 'acrescimo', 'valor' => 0.0000, 'prioridade' => 4],
            ];
            foreach ($regras as $regra) {
                $payload = $this->filtrarColunas('precificacao_regras', [
                    'precificacao_id' => $produto->id,
                    'tipo' => $regra['tipo'],
                    'valor' => $regra['valor'],
                    'prioridade' => $regra['prioridade'],
                    'ativo' => 1,
                ]);
                $model = new PrecificacaoRegra();
                $model->fill($payload);
                $model->save();
                $count++;
            }
        }

        return $count;
    }

    private function resolverProdutoLegadoId(int $empresaId, string $nome): ?int
    {
        if (! class_exists(Produto::class)) {
            return null;
        }

        $query = Produto::query()->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)]);
        if (PrecificacaoSchema::hasColumn('produtos', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        $produto = $query->first();
        return $produto?->id;
    }

    private function filtrarColunas(string $tabela, array $dados): array
    {
        return collect($dados)
            ->filter(fn ($valor, $coluna) => PrecificacaoSchema::hasColumn($tabela, $coluna))
            ->all();
    }
}
