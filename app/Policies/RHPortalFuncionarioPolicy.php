<?php

namespace App\Policies;

use App\Models\RHPortalFuncionario;
use App\Models\Usuario;
use App\Services\RH\RHAccessControlService;

class RHPortalFuncionarioPolicy
{
    public function __construct(private RHAccessControlService $acl)
    {
    }

    public function manageAccess(?Usuario $user = null, ?RHPortalFuncionario $acesso = null): bool
    {
        return $this->acl->userCan('rh.acl.gerenciar', $user);
    }

    public function configure(?Usuario $user = null, ?RHPortalFuncionario $acesso = null): bool
    {
        return $this->manageAccess($user, $acesso);
    }

    public function sendInvite(?Usuario $user = null, ?RHPortalFuncionario $acesso = null): bool
    {
        return $this->manageAccess($user, $acesso);
    }
}
