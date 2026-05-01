<?php

namespace App\Modules\RH\Application\DTOs;

use App\Models\Funcionario;

final class ChangeEmployeeStatusData
{
    public function __construct(
        public readonly Funcionario $funcionario,
        public readonly string $status,
        public readonly ?int $usuarioId = null,
        public readonly ?string $motivo = null,
    ) {
    }
}
