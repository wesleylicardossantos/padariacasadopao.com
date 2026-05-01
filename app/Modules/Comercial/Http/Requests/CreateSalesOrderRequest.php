<?php

namespace App\Modules\Comercial\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['nullable', 'integer'],
            'filial_id' => ['nullable', 'integer'],
            'nome' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'observacao' => ['nullable', 'string'],
            'rua' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:60'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produto_id' => ['required', 'integer'],
            'items.*.quantidade' => ['required', 'numeric', 'gt:0'],
            'items.*.valor' => ['required', 'numeric', 'min:0'],
            'items.*.observacao' => ['nullable', 'string'],
        ];
    }
}
