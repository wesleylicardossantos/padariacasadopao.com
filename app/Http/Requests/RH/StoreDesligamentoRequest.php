<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesligamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'funcionario_id' => ['required', 'integer'],
            'data_desligamento' => ['required', 'date'],
            'motivo' => ['required', 'max:255'],
            'tipo' => ['required', 'max:100'],
            'tipo_aviso' => ['nullable', 'max:40'],
            'dependentes_irrf' => ['nullable', 'integer', 'min:0', 'max:20'],
            'descontos_extras' => ['nullable', 'numeric', 'min:0'],
            'observacao' => ['nullable', 'string'],
            'gerar_trct' => ['nullable', 'boolean'],
            'gerar_tqrct' => ['nullable', 'boolean'],
            'gerar_homologacao' => ['nullable', 'boolean'],
            'bloquear_portal' => ['nullable', 'boolean'],
            'arquivo_morto' => ['nullable', 'boolean'],
        ];
    }
}
