<?php

namespace App\Modules\RH\Application\DTOs;

use Illuminate\Http\Request;

final class CreateEmployeeData
{
    public function __construct(
        public readonly Request $request,
        public readonly int $empresaId,
    ) {
    }

    public static function fromRequest(Request $request, int $empresaId): self
    {
        return new self($request, $empresaId);
    }
}
