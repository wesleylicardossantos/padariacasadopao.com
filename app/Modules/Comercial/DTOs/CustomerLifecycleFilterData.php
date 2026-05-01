<?php

namespace App\Modules\Comercial\DTOs;

use Illuminate\Http\Request;

class CustomerLifecycleFilterData
{
    public function __construct(
        public readonly int $empresaId,
        public readonly mixed $filialId,
        public readonly ?string $search,
        public readonly ?string $documento,
        public readonly int $perPage,
    ) {
    }

    public static function fromRequest(Request $request, int $empresaId): self
    {
        return new self(
            empresaId: $empresaId,
            filialId: $request->input('filial_id', 'todos'),
            search: self::nullableString($request->input('search')),
            documento: self::normalizeDocumento($request->input('cpf_cnpj')),
            perPage: max(1, min(100, (int) $request->input('per_page', 20))),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private static function normalizeDocumento(mixed $value): ?string
    {
        $value = preg_replace('/\D+/', '', (string) $value);
        return $value === '' ? null : $value;
    }
}
