<?php

namespace App\Modules\RH\Application\Actions;

use App\Models\RHPortalFuncionario;
use App\Modules\RH\Application\DTOs\GrantPortalAccessData;
use App\Services\RHPortalAcessoService;

final class GrantPortalAccessAction
{
    public function __construct(private RHPortalAcessoService $service)
    {
    }

    public function execute(GrantPortalAccessData $data): RHPortalFuncionario
    {
        $acesso = $this->service->criarOuObter($data->funcionario);
        $acesso->perfil_id = $data->perfilId;
        $acesso->ativo = $data->ativo;
        $acesso->permissoes_extras = array_values(array_unique($data->permissoesExtras));
        $acesso->empresa_id = (int) $data->funcionario->empresa_id;
        $acesso->save();

        return $acesso->fresh(['perfil']);
    }
}
