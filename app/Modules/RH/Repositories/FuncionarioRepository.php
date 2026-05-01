<?php

namespace App\Modules\RH\Repositories;

use App\Models\Funcionario;

class FuncionarioRepository
{
    public function queryByEmpresa($empresaId)
    {
        return Funcionario::where('empresa_id', $empresaId);
    }

    public function ativosByEmpresa($empresaId)
    {
        return $this->queryByEmpresa($empresaId)
            ->where(function ($q) {
                $q->whereNull('ativo')->orWhere('ativo', 1);
            });
    }

    public function findByEmpresaOrFail($empresaId, $id)
    {
        return $this->queryByEmpresa($empresaId)->findOrFail($id);
    }
}
