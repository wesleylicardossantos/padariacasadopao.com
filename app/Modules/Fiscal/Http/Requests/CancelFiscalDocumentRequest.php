<?php

namespace App\Modules\Fiscal\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelFiscalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['nullable', 'integer'],
            'reason' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}
