<?php

namespace App\Modules\Estoque\Http\Requests;

class StockAdjustmentRequest extends BaseStockMovementRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'quantity' => ['required', 'numeric'],
        ]);
    }
}
