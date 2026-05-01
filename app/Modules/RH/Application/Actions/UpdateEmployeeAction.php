<?php

namespace App\Modules\RH\Application\Actions;

use App\Models\Funcionario;
use App\Modules\RH\Application\DTOs\UpdateEmployeeData;
use App\Modules\RH\Application\Funcionario\FuncionarioService;

final class UpdateEmployeeAction
{
    public function __construct(private FuncionarioService $service)
    {
    }

    public function execute(UpdateEmployeeData $data): Funcionario
    {
        return $this->service->update($data->funcionario, $data->request, $data->empresaId);
    }
}
