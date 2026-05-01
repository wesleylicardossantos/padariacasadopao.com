<?php

namespace App\Policies;

use App\Models\RHPortalPerfil;
use App\Models\Usuario;
use App\Services\RH\RHAccessControlService;

class RHPortalPerfilPolicy
{
    public function __construct(private RHAccessControlService $acl)
    {
    }

    public function viewAny(?Usuario $user = null): bool
    {
        return $this->acl->userCan('rh.acl.gerenciar', $user);
    }

    public function view(?Usuario $user = null, RHPortalPerfil $perfil): bool
    {
        return $this->viewAny($user);
    }

    public function create(?Usuario $user = null): bool
    {
        return $this->acl->userCan('rh.acl.gerenciar', $user);
    }

    public function update(?Usuario $user = null, RHPortalPerfil $perfil): bool
    {
        return $this->acl->userCan('rh.acl.gerenciar', $user);
    }

    public function delete(?Usuario $user = null, RHPortalPerfil $perfil): bool
    {
        return $this->acl->userCan('rh.acl.gerenciar', $user);
    }
}
