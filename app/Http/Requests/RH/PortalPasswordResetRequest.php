<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class PortalPasswordResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'senha' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
