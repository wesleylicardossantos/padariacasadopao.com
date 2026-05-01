<?php

namespace App\Modules\RH\Application\Actions;

use App\Models\HistoricoFuncionario;
use App\Modules\RH\Application\DTOs\ChangeEmployeeStatusData;
use App\Modules\RH\Support\Enums\EmployeeStatus;
use Illuminate\Support\Facades\DB;

final class ChangeEmployeeStatusAction
{
    public function execute(ChangeEmployeeStatusData $data): void
    {
        DB::transaction(function () use ($data) {
            $funcionario = $data->funcionario;
            $anterior = $funcionario->ativo;
            $novo = EmployeeStatus::toAtivoColumn($data->status);

            $funcionario->ativo = $novo;
            $funcionario->save();

            HistoricoFuncionario::create([
                'funcionario_id' => $funcionario->id,
                'tipo' => 'status',
                'descricao' => $data->motivo ?: 'Status atualizado via fluxo refatorado de RH.',
                'valor_anterior' => EmployeeStatus::isActiveValue($anterior) ? 1 : 0,
                'valor_novo' => EmployeeStatus::isActiveValue($novo) ? 1 : 0,
            ]);
        });
    }
}
