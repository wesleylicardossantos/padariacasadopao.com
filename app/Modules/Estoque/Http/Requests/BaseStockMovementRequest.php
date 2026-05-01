<?php

namespace App\Modules\Estoque\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['required', 'integer'],
            'filial_id' => ['nullable', 'integer'],
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'numeric'],
            'unit_cost' => ['nullable', 'numeric'],
            'source' => ['nullable', 'string', 'max:100'],
            'source_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
