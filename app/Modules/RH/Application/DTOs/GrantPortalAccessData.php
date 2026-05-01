<?php

namespace App\Modules\RH\Application\DTOs;

use App\Models\Funcionario;

final class GrantPortalAccessData
{
    public function __construct(
        public readonly Funcionario $funcionario,
        public readonly ?int $perfilId = null,
        public readonly bool $ativo = true,
        public readonly array $permissoesExtras = [],
    ) {
    }
}
