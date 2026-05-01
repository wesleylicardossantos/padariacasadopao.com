<?php

namespace App\Modules\RH\Application\Actions;

use App\Models\RHPortalFuncionario;
use App\Modules\RH\Application\DTOs\AuthenticatePortalUserData;
use App\Modules\RH\Application\Queries\PortalEmployeeLookupQuery;
use App\Services\RHPortalAcessoService;

final class AuthenticatePortalUserAction
{
    public function __construct(
        private PortalEmployeeLookupQuery $lookup,
        private RHPortalAcessoService $portalAcessoService,
    ) {
    }

    public function execute(AuthenticatePortalUserData $data): array
    {
        $funcionario = $this->lookup->findByLogin($data->login);
        if (!$funcionario) {
            return ['ok' => false, 'message' => 'CPF ou e-mail não localizado no portal do funcionário.'];
        }

        if (isset($funcionario->ativo) && (int) $funcionario->ativo === 0) {
            return ['ok' => false, 'message' => 'Funcionário inativo ou desligado.'];
        }

        $acesso = $this->portalAcessoService->criarOuObter($funcionario);
        if (!$acesso->ativo) {
            return ['ok' => false, 'message' => 'Portal desativado para este funcionário.'];
        }

        if (!$this->portalAcessoService->senhaValida($acesso, $data->senha)) {
            $mensagemBloqueio = $this->portalAcessoService->mensagemBloqueio($acesso);
            return ['ok' => false, 'message' => $mensagemBloqueio ?: 'Senha inválida.'];
        }

        $this->portalAcessoService->registrarLogin($acesso, $data->ip);

        return [
            'ok' => true,
            'funcionario' => $funcionario,
            'acesso' => $acesso,
        ];
    }
}
