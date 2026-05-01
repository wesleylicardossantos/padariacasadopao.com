<?php

namespace App\Modules\Comercial\UseCases;

use App\Modules\Comercial\Repositories\SalesOrderRepository;
use App\Modules\Comercial\Services\CommercialAuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateSalesOrderUseCase
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
                throw new InvalidArgumentException('Informe ao menos um item para o pedido.');
            }

            $order = $this->repository->createOrder([
                'empresa_id' => $empresaId,
                'cliente_id' => $payload['cliente_id'] ?? null,
                'nome' => $payload['nome'] ?? null,
                'telefone' => $payload['telefone'] ?? null,
                'observacao' => $payload['observacao'] ?? null,
                'rua' => $payload['rua'] ?? null,
                'numero' => $payload['numero'] ?? null,
                'referencia' => $payload['referencia'] ?? null,
                'status' => $payload['status'] ?? 0,
            ]);

            foreach ($items as $item) {
                $this->repository->createOrderItem([
                    'pedido_id' => $order->id,
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'valor' => $item['valor'],
                    'observacao' => $item['observacao'] ?? null,
                    'status' => $item['status'] ?? 0,
                ]);
            }

            $this->auditService->record([
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'entidade' => 'pedido',
                'entidade_id' => $order->id,
                'acao' => 'criado',
                'depois' => $order->fresh('itens')->toArray(),
            ]);

            return $order->fresh('itens');
        });
    }
}
