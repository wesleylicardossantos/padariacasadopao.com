<?php

namespace App\Modules\RH\Application\Actions;

use App\Modules\RH\Application\DTOs\PortalRecoveryData;
use App\Modules\RH\Application\Queries\PortalEmployeeLookupQuery;
use App\Services\RHPortalAcessoService;

final class BuildPortalRecoveryAction
{
    public function __construct(
        private PortalEmployeeLookupQuery $lookup,
        private RHPortalAcessoService $portalAcessoService,
    ) {
    }

    public function execute(PortalRecoveryData $data): array
    {
        $funcionario = $this->lookup->findByLogin($data->login);
        if (!$funcionario) {
            return ['ok' => false, 'message' => 'CPF ou e-mail não localizado.'];
        }

        $acesso = $this->portalAcessoService->gerarTokenRecuperacao($funcionario);

        return [
            'ok' => true,
            'funcionario' => $funcionario,
            'acesso' => $acesso,
            'canal' => $data->canal,
            'link' => $this->portalAcessoService->recuperacaoUrl($acesso),
        ];
    }
}
