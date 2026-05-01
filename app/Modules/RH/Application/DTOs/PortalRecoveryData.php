<?php

namespace App\Modules\RH\Application\DTOs;

final class PortalRecoveryData
{
    public function __construct(
        public string $login,
        public string $canal = 'email',
    ) {
    }
}
