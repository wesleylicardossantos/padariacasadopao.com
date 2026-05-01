<?php

namespace App\Modules\Comercial\UseCases;

use App\Modules\Comercial\Repositories\SalesOrderRepository;
use App\Modules\Comercial\Services\CommercialAuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateBudgetUseCase
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
                throw new InvalidArgumentException('Informe ao menos um item para o orçamento.');
            }

            $subtotal = 0.0;
            foreach ($items as $item) {
                $subtotal += (float) $item['quantidade'] * (float) $item['valor'];
            }

            $desconto = (float) ($payload['desconto'] ?? 0);
            $acrescimo = (float) ($payload['acrescimo'] ?? 0);
            $total = max(0, $subtotal - $desconto + $acrescimo);

            $budget = $this->repository->createBudget([
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
                'validade' => $payload['validade'] ?? now()->addDays(7),
                'estado' => $payload['estado'] ?? 'ABERTO',
            ]);

            foreach ($items as $item) {
                $this->repository->createBudgetItem([
                    'orcamento_id' => $budget->id,
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'valor' => $item['valor'],
                ]);
            }

            $this->auditService->record([
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'entidade' => 'orcamento',
                'entidade_id' => $budget->id,
                'acao' => 'criado',
                'depois' => $budget->fresh('itens')->toArray(),
                'meta' => [
                    'subtotal' => $subtotal,
                    'desconto' => $desconto,
                    'acrescimo' => $acrescimo,
                ],
            ]);

            return $budget->fresh('itens');
        });
    }
}
