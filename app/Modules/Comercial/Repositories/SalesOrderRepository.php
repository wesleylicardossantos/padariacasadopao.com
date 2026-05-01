<?php

namespace App\Modules\Comercial\Repositories;

use App\Models\ItemOrcamento;
use App\Models\ItemPedido;
use App\Models\ItemVenda;
use App\Models\Orcamento;
use App\Models\Pedido;
use App\Models\Venda;

class SalesOrderRepository
{
    public function createSale(array $attributes): Venda
    {
        return Venda::query()->create($attributes);
    }

    public function createSaleItem(array $attributes): ItemVenda
    {
        return ItemVenda::query()->create($attributes);
    }

    public function createOrder(array $attributes): Pedido
    {
        return Pedido::query()->create($attributes);
    }

    public function createOrderItem(array $attributes): ItemPedido
    {
        return ItemPedido::query()->create($attributes);
    }

    public function createBudget(array $attributes): Orcamento
    {
        return Orcamento::query()->create($attributes);
    }

    public function createBudgetItem(array $attributes): ItemOrcamento
    {
        return ItemOrcamento::query()->create($attributes);
    }
}
