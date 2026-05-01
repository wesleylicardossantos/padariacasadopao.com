<?php

namespace App\Modules\Fiscal\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrepareFiscalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'max:30'],
            'reference' => ['nullable', 'string', 'max:100'],
            'sale' => ['required', 'array'],
            'sale.id' => ['nullable', 'integer'],
            'sale.itens' => ['required', 'array', 'min:1'],
            'sale.pagamentos' => ['nullable', 'array'],
            'sale.desconto' => ['nullable', 'numeric'],
            'sale.acrescimo' => ['nullable', 'numeric'],
            'company' => ['required', 'array'],
            'company.id' => ['nullable', 'integer'],
            'company.razao_social' => ['nullable', 'string', 'max:255'],
            'company.cnpj' => ['nullable', 'string', 'max:20'],
            'customer' => ['nullable', 'array'],
        ];
    }
}
