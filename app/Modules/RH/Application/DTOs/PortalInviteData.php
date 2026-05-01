<?php

namespace App\Modules\RH\Application\DTOs;

use App\Models\Funcionario;

final class PortalInviteData
{
    public function __construct(
        public Funcionario $funcionario,
        public string $canal = 'whatsapp',
    ) {
    }
}
