<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class PortalAdminConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ativo' => ['nullable', 'boolean'],
            'perfil_id' => ['nullable', 'integer', 'min:0'],
            'pode_ver_relatorio_produtos' => ['nullable', 'boolean'],
            'pode_ver_relatorio_produtos_extra' => ['nullable', 'boolean'],
        ];
    }
}
