<?php

namespace App\Modules\RH\Services;

use App\Modules\RH\Repositories\FuncionarioRepository;

class FolhaService
{
    protected $funcionarios;

    public function __construct(FuncionarioRepository $funcionarios)
    {
        $this->funcionarios = $funcionarios;
    }

    public function listarResumo(int $empresaId): array
    {
        $funcionarios = $this->funcionarios->listComEventos($empresaId);

        return [
            'empresaId' => $empresaId,
            'funcionarios' => $funcionarios,
            'totalFuncionarios' => $funcionarios->count(),
            'totalFolha' => (float) $funcionarios->sum('salario'),
        ];
    }
}
