<?php

namespace App\Modules\RH\Application\DTOs;

final class AuthenticatePortalUserData
{
    public function __construct(
        public string $login,
        public string $senha,
        public ?string $ip = null,
    ) {
    }
}
