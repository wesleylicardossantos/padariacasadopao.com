<?php

namespace App\Modules\Comercial\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSaleRequest extends FormRequest
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
            'usuario_id' => ['nullable', 'integer'],
            'forma_pagamento' => ['nullable', 'string', 'max:60'],
            'tipo_pagamento' => ['nullable', 'string', 'max:10'],
            'observacao' => ['nullable', 'string'],
            'desconto' => ['nullable', 'numeric', 'min:0'],
            'acrescimo' => ['nullable', 'numeric', 'min:0'],
            'data_emissao' => ['nullable', 'date'],
            'comissao.funcionario_id' => ['nullable', 'integer'],
            'comissao.valor' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produto_id' => ['required', 'integer'],
            'items.*.quantidade' => ['required', 'numeric', 'gt:0'],
            'items.*.valor' => ['required', 'numeric', 'min:0'],
        ];
    }
}
