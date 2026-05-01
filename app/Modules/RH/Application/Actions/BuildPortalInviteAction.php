<?php

namespace App\Modules\RH\Application\Actions;

use App\Modules\RH\Application\DTOs\PortalInviteData;
use App\Services\RHPortalAcessoService;

final class BuildPortalInviteAction
{
    public function __construct(private RHPortalAcessoService $portalAcessoService)
    {
    }

    public function execute(PortalInviteData $data): array
    {
        $acesso = $this->portalAcessoService->gerarTokenPrimeiroAcesso($data->funcionario);

        return [
            'funcionario' => $data->funcionario,
            'acesso' => $acesso,
            'canal' => $data->canal,
            'link' => $this->portalAcessoService->primeiroAcessoUrl($acesso),
        ];
    }
}
