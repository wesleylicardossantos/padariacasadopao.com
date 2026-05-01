<?php

namespace App\Modules\Estoque\Http\Requests;

class StockEntryRequest extends BaseStockMovementRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'quantity' => ['required', 'numeric', 'gt:0'],
        ]);
    }
}
