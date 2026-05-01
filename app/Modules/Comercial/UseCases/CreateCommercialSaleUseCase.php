<?php

namespace App\Modules\Comercial\UseCases;

use App\Models\ComissaoVenda;
use App\Modules\Comercial\Repositories\SalesOrderRepository;
use App\Modules\Comercial\Services\CommercialAuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateCommercialSaleUseCase
{
    public function __construct(
        private readonly SalesOrderRepository $repository,
        private readonly CommercialAuditService $auditService,
    ) {
    }

    public function handle(int $empresaId, array $payload, ?int $usuarioId = null)
    {
        return DB::transaction(function () use ($empresaId, $payload, $usuarioId) {
            $items = $payload['items'] ?? [];
            if (count($items) === 0) {
                throw new InvalidArgumentException('Informe ao menos um item para a venda.');
            }

            $subtotal = 0.0;
            foreach ($items as $item) {
                $subtotal += (float) $item['quantidade'] * (float) $item['valor'];
            }

            $desconto = (float) ($payload['desconto'] ?? 0);
            $acrescimo = (float) ($payload['acrescimo'] ?? 0);
            $total = max(0, $subtotal - $desconto + $acrescimo);

            $sale = $this->repository->createSale([
                'empresa_id' => $empresaId,
                'filial_id' => $payload['filial_id'] ?? null,
                'cliente_id' => $payload['cliente_id'] ?? null,
                'usuario_id' => $payload['usuario_id'] ?? $usuarioId,
                'forma_pagamento' => $payload['forma_pagamento'] ?? 'dinheiro',
                'tipo_pagamento' => $payload['tipo_pagamento'] ?? null,
                'observacao' => $payload['observacao'] ?? null,
                'desconto' => $desconto,
                'acrescimo' => $acrescimo,
                'valor_total' => $total,
                'data_emissao' => $payload['data_emissao'] ?? now(),
            ]);

            foreach ($items as $index => $item) {
                $this->repository->createSaleItem([
                    'venda_id' => $sale->id,
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'valor' => $item['valor'],
                    'num_item_pedido' => $item['num_item_pedido'] ?? ($index + 1),
                    'x_pedido' => $item['x_pedido'] ?? null,
                ]);
            }

            if (($payload['comissao']['funcionario_id'] ?? null) && isset($payload['comissao']['valor'])) {
                ComissaoVenda::query()->create([
                    'funcionario_id' => $payload['comissao']['funcionario_id'],
                    'venda_id' => $sale->id,
                    'tabela' => 'vendas',
                    'valor' => $payload['comissao']['valor'],
                    'status' => 0,
                    'empresa_id' => $empresaId,
                ]);
            }

            $this->auditService->record([
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'entidade' => 'venda',
                'entidade_id' => $sale->id,
                'acao' => 'criada',
                'depois' => $sale->fresh('itens')->toArray(),
                'meta' => [
                    'subtotal' => $subtotal,
                    'desconto' => $desconto,
                    'acrescimo' => $acrescimo,
                ],
            ]);

            return $sale->fresh('itens');
        });
    }
}
