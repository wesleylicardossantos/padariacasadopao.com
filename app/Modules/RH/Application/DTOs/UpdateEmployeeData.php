<?php

namespace App\Modules\RH\Application\DTOs;

use App\Models\Funcionario;
use Illuminate\Http\Request;

final class UpdateEmployeeData
{
    public function __construct(
        public readonly Funcionario $funcionario,
        public readonly Request $request,
        public readonly int $empresaId,
    ) {
    }

    public static function fromRequest(Funcionario $funcionario, Request $request, int $empresaId): self
    {
        return new self($funcionario, $request, $empresaId);
    }
}
