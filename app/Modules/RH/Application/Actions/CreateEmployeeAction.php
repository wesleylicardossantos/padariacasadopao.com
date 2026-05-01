<?php

namespace App\Modules\RH\Application\Actions;

use App\Models\Funcionario;
use App\Modules\RH\Application\DTOs\CreateEmployeeData;
use App\Modules\RH\Application\Funcionario\FuncionarioService;

final class CreateEmployeeAction
{
    public function __construct(private FuncionarioService $service)
    {
    }

    public function execute(CreateEmployeeData $data): Funcionario
    {
        return $this->service->store($data->request, $data->empresaId);
    }
}
